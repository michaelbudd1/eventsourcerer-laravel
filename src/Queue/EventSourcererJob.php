<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

final class EventSourcererJob extends Job implements JobContract
{
    protected $job;

    protected $payload;

    protected $connectionName;

    private array $event;

    public function __construct(
        Container $container,
        string $payload,
        array $event,
        string $queue,
        string $connectionName
    ) {
        $this->queue = $queue;
        $this->payload = $payload;
        $this->event = $event;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }

    public function attempts(): int
    {
        return 1;
    }

    public function getJobId(): string
    {
        return (string) $this->event['allSequence'];
    }

    public function getRawBody(): string
    {
        return $this->payload;
    }
}
