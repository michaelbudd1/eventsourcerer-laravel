<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

final class EventSourcererJob extends Job implements JobContract
{
    public function __construct(protected $container, protected $queue, private readonly array $event)
    {
    }

    public function getJobId(): int
    {
        return $this->event['allSequence'];
    }

    public function getRawBody(): string
    {
        return json_encode($this->event, JSON_THROW_ON_ERROR);
    }

    public function attempts(): int
    {
        return 0;
    }
}
