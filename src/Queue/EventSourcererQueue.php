<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;

final class EventSourcererQueue extends Queue implements QueueContract
{
    private const string CONNECTION_NAME = 'eventsourcerer';

    public function __construct(
        private readonly Client $client,
        private readonly ApplicationId $applicationId
    ) {}

    public function size($queue = null): int
    {
        // we can't provide this
        return 1;
    }

    public function push($job, $data = '', $queue = null): void
    {
        // TODO: Implement push() method.
    }

    public function pushRaw($payload, $queue = null, array $options = []): void
    {
        // TODO: Implement pushRaw() method.
    }

    public function later($delay, $job, $data = '', $queue = null): void
    {
        // TODO: Implement later() method.
    }

    public function pop($queue = null): ?Job
    {
        $event = $this->client->fetchOneMessage($this->applicationId);

        if (null === $event) {
            return null;
        }

        ;

        return new EventSourcererJob(
            $this->container,
            $this->createPayload(new NewEventJob($event), $queue),
            $event,
            $queue,
            self::CONNECTION_NAME
        );
    }
}
