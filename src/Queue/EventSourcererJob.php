<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class EventSourcererJob extends Job implements JobContract
{
    protected $job;

    protected $payload;

    protected $connectionName;

    private array $event;
    private EventSourcererQueue $queueObject;

    public function __construct(
        Container $container,
        EventSourcererQueue $queueObject,
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
        $this->queueObject = $queueObject;
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

    public function delete(): void
    {
        parent::delete();

        $this->queueObject->removeFromQueue($this->event);
    }
}
