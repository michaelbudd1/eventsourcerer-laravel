<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\ListenForEvents;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\RemoveEventFromQueue;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent as WriteNewEventCommand;
use Eventsourcerer\EventSourcererLaravel\Exception\QueueCannotProcessJob;
use Eventsourcerer\EventSourcererLaravel\Repository\WorkerEvents;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Support\Facades\Process;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

final class EventSourcererQueue extends Queue implements QueueContract
{
    private const string CONNECTION_NAME = 'eventsourcerer';
    private WorkerId $workerId;

    /**
     * @var resource
     */
    private $localConnection;

    public function __construct(
        private readonly WorkerEvents $workerEvents,
        private readonly Client $client,
    ) {
        $this->workerId = self::workerId();

        Process::start($this->startListenerCommand());

        sleep(1);

        $this->localConnection = $this->client->createLocalConnection();
    }

    public function size($queue = null): int
    {
        return $this->workerEvents->countFor($this->workerId);
    }

    public function push($job, $data = '', $queue = null): void
    {
        if (!($job instanceof WriteNewEvent)) {
            throw QueueCannotProcessJob::becauseTheJobTypeIsInvalid($job::class);
        }

        Process::start(
            self::writeEventCommand(
                $job->streamId,
                $job->eventName,
                $job->eventVersion,
                $job->payload
            )
        );
    }

    public function pushRaw($payload, $queue = null, array $options = []): void
    {
    }

    public function later($delay, $job, $data = '', $queue = null): void
    {
        $this->push($job, $data, $queue);
    }

    public function pop($queue = null): ?Job
    {
        $event = $this->workerEvents->popFor($this->workerId);

        if (null !== $event) {
//            dump(
//                sprintf(
//                    'dispatched job with sequence %d',
//                    $event['number']
//                )
//            );

            $this->client->acknowledgeEvent(
                StreamId::fromString($event['stream']),
                StreamId::fromString($event['catchupRequestStream']),
                $this->workerId,
                Checkpoint::fromInt($event['number']),
                Checkpoint::fromInt($event['allSequence']),
                $this->localConnection
            );

            return new EventSourcererJob(
                $this->container,
                $this,
                $this->createPayload(new NewEventJob($event), $queue),
                $event,
                $queue,
                self::CONNECTION_NAME
            );
        }

        return null;
    }

    public function removeFromQueue(array $event): void
    {
        Process::start(
            sprintf(
                'php artisan %s %d %d',
                RemoveEventFromQueue::SIGNATURE_PREFIX,
                $event['number'],
                $event['allSequence']
            )
        );
    }

    private function startListenerCommand(): string
    {
        $signature = str_replace(
            '{worker}',
            $this->workerId->toString(),
            ListenForEvents::SIGNATURE
        );

        return sprintf('php artisan %s', $signature);
    }

    private static function writeEventCommand(
        StreamId $streamId,
        EventName $eventName,
        EventVersion $eventVersion,
        array $payload
    ): string {
        return sprintf(
            'php artisan %s %s %s %s %s',
            WriteNewEventCommand::SIGNATURE_PREFIX,
            $streamId,
            $eventName,
            $eventVersion,
            '\'' . json_encode($payload, JSON_THROW_ON_ERROR) . '\''
        );
    }

    private static function workerId(): WorkerId
    {
        return WorkerId::fromString(
            sprintf('worker-%s', bin2hex(random_bytes(5)))
        );
    }
}
