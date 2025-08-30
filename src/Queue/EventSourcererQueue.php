<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\ListenForEvents;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\RemoveEventFromQueue;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent as WriteNewEventCommand;
use Eventsourcerer\EventSourcererLaravel\Exception\QueueCannotProcessJob;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Support\Facades\Process;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class EventSourcererQueue extends Queue implements QueueContract
{
    private const string CONNECTION_NAME = 'eventsourcerer';

    public function __construct(
        private readonly Client $client,
        private readonly ApplicationId $applicationId
    ) {
        Process::start(self::startServerCommand());
    }

    public function size($queue = null): int
    {
        return $this->client->availableEventsCount($this->applicationId);
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
        $event = $this->client->fetchOneMessage();

        if (null === $event) {
            return null;
        }

        return new EventSourcererJob(
            $this->container,
            $this,
            $this->createPayload(new NewEventJob($event), $queue),
            $event,
            $queue,
            self::CONNECTION_NAME
        );
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

    private static function startServerCommand(): string
    {
        return sprintf(
            'php artisan %s',
            ListenForEvents::SIGNATURE
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
}
