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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class EventSourcererQueue extends Queue implements QueueContract
{
    private const string CONNECTION_NAME = 'eventsourcerer';

    public function __construct(private readonly WorkerEvents $workerEvents)
    {
        Process::start(self::startListenerCommand());
    }

    public function size($queue = null): int
    {
        return 0;
//        return $this->workerEvents->countFor();
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
//        $event = $this->workerEvents->pop();
        dd($this->getConnectionName());

        $events = Cache::get(ListenForEvents::EVENTS_CACHE_KEY, []);
        $reversed = array_reverse($events);
        $event = array_pop($reversed);

        if (null !== $event) {
            unset($events[$event['allSequence']]);

            Cache::set(ListenForEvents::EVENTS_CACHE_KEY, $events);

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

    private static function startListenerCommand(): string
    {
        return sprintf(
            'php artisan %s %s',
            ListenForEvents::SIGNATURE,
            self::workerName(),
        );
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

    private static function workerName(): string
    {
        return sprintf(
            'worker-%s',
            random_bytes(5)
        );
    }
}
