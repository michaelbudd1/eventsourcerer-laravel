<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\LaravelClient\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class WriteNewEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StreamId $streamId,
        public EventName $eventName,
        public EventVersion $eventVersion,
        public array $payload
    ) {}

    public function handle(): void
    {
        // serves as DTO only
    }
}
