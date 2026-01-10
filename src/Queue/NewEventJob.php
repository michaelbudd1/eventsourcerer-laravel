<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class NewEventJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array{
     *     allSequence: int,
     *     eventVersion: int,
     *     name: string,
     *     number: int,
     *     payload: array,
     *     stream: string,
     *     occurred: string,
     *     workerId: string,
     *     catchupRequestStream: string
     * } $event
     */
    public function __construct(private readonly array $event) {}

    public function handle(): void
    {
        dump(
            sprintf(
                'Received event from stream "%s"; with type "%s"; with sequence "%d"',
                $this->event['stream'],
                $this->event['name'],
                $this->event['number']
            )
        );

        sleep(2);
    }
}
