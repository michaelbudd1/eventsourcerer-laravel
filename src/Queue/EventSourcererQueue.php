<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Queue\Jobs\Job;
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

    public function size($queue = null)
    {
        // TODO: Implement size() method.
    }

    public function push($job, $data = '', $queue = null)
    {
        // TODO: Implement push() method.
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        // TODO: Implement pushRaw() method.
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        // TODO: Implement later() method.
    }

    public function pop($queue = null): ?EventSourcererJob
    {
        $event = $this->client->fetchOneMessage($this->applicationId);

        if (null === $event) {
            return null;
        }

        return new EventSourcererJob($this->container, $event, $queue);
    }

    public function __call(string $name, array $arguments)
    {
    }
}
