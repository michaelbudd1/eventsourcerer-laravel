<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;

final class EventSourcererQueue extends Queue implements QueueContract
{
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

        return new SyncJob(
            $this->container,
            json_encode($event, JSON_THROW_ON_ERROR),
            'eventsourcerer',
            $queue
        );

//        return new EventSourcererJob($this->container, $event, $queue);
    }
}
