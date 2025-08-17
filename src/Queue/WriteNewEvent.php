<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Contracts\Queue\ShouldQueue;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final readonly class WriteNewEvent implements ShouldQueue
{
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
