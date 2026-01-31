<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\ListenForEvents;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent as WriteNewEventCommand;
use Eventsourcerer\EventSourcererLaravel\Exception\QueueCannotProcessJob;
use Eventsourcerer\EventSourcererLaravel\Repository\WorkerEvents;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
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
    private const int MAX_ATTEMPTS_TO_ESTABLISH_LOCAL_CONNECTION = 30;
    private const int ONE_HUNDRED_MILLISECONDS_IN_MICROSECONDS = 100_000;
    private const  int NUMBER_OF_CHARS_FOR_RANDOM_WORKER_ID = 5;

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

        $this->startListenerCommand();
        $this->waitForSocket();

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
        if (!is_resource($this->localConnection) || feof($this->localConnection)) {
            $this->localConnection = $this->client->createLocalConnection();

            sleep(1);
        }

        $this->client->acknowledgeEvent(
            StreamId::fromString($event['stream']),
            StreamId::fromString($event['catchupRequestStream'] ?? $event['stream']),
            $this->workerId,
            Checkpoint::fromInt($event['number']),
            Checkpoint::fromInt($event['allSequence']),
            $this->localConnection
        );
    }

    private function startListenerCommand(): void
    {
        $signature = str_replace(
            '{worker}',
            $this->workerId->toString(),
            ListenForEvents::SIGNATURE
        );

        $command = sprintf('php artisan %s', $signature);

        Process::start($command);
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
            sprintf('worker-%s', bin2hex(random_bytes(self::NUMBER_OF_CHARS_FOR_RANDOM_WORKER_ID)))
        );
    }
    
    private function waitForSocket(): void
    {
        $maxAttempts = self::MAX_ATTEMPTS_TO_ESTABLISH_LOCAL_CONNECTION;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            if (file_exists(Client::IPC_URI)) {
                usleep(self::ONE_HUNDRED_MILLISECONDS_IN_MICROSECONDS);

                return;
            }
            
            usleep(self::ONE_HUNDRED_MILLISECONDS_IN_MICROSECONDS);
            $attempt++;
        }
        
        throw new \RuntimeException('Listener socket did not become available within 30 seconds');
    }
}
