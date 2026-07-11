<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\LaravelClient\Queue;

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
            '%s %s %s %s at %s',
            $this->event['name'],
            $this->event['stream'],
            $this->event['allSequence'],
            $this->event['number'],
            date('H:i:s')
        );

        $logFile = sprintf('%s-%s.log', $this->event['workerId'], Carbon::now()->format('Ymd'));

        $fh = fopen(storage_path($logFile), 'a+');
        fwrite($fh, $message . PHP_EOL);
        fclose($fh);
    }
}
