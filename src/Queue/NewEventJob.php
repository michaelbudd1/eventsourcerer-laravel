<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewEventJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(private readonly array $event) {}

    public function handle(): void
    {
        dump($this->event);
    }

    public function uniqueId(): string
    {
        dd($this->event);
        return $this->event['streamId'];
    }
}
