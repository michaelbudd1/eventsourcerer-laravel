<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

final class EventSourcererJob extends Job implements JobContract
{
    private const string EVENTSOURCERER = 'eventsourcerer';

    protected $job;

    protected $payload;

    public function __construct(Container $container, array $payload, string $queue)
    {
        $this->queue = $queue;
        $this->payload = $payload;
        $this->container = $container;
        $this->connectionName = self::EVENTSOURCERER;
    }

    public function attempts(): int
    {
        return 1;
    }

    public function getJobId(): string
    {
        return (string) $this->payload['allSequenceId'];
    }

    public function getRawBody(): string
    {
        $payload        = $this->payload;
        $payload['job'] = NewEventJob::class;

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    public function getQueue(): string
    {
        return self::EVENTSOURCERER;
    }
}
