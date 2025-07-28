<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

final class EventSourcererJob extends Job implements JobContract
{
    protected $job;

    protected $event;

    protected $connectionName;

    public function __construct(Container $container, array $event, string $queue, string $connectionName)
    {
        $this->queue = $queue;
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
        return json_encode($this->event, JSON_THROW_ON_ERROR);
    }

    public function payload(): array
    {
        return [
            'job' => sprintf('%s@%s', NewEventJob::class, 'handle'),
            'data' => $this->event,
        ];
    }
}
