<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

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
        $message = sprintf(
            'Received event from stream "%s"; with type "%s"; with sequence "%d" and ALL sequence "%d"',
            $this->event['stream'],
            $this->event['name'],
            $this->event['number'],
            $this->event['allSequence']
        );

        $logFile = sprintf('worked-%s-%s.log', $this->event['workerId'], Carbon::now()->format('Ymd'));

        $fh = fopen(storage_path($logFile), 'a+');
        fwrite($fh, $message . PHP_EOL);
        fclose($fh);
    }
}
