<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class NewEventJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(private array $event) {}

    public function handle(): void
    {
        dump($this->event);

        sleep(2);
    }

    public function uniqueId(): string
    {
        return $this->event['stream'];
    }
}
