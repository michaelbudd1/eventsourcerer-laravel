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
        private StreamId $streamId,
        private EventName $eventName,
        private EventVersion $eventVersion,
        private array $payload
    ) {}

    public function handle(Client $client): void
    {
        $client->writeNewEvent(
            $this->streamId,
            $this->eventName,
            $this->eventVersion,
            $this->payload
        );
    }
}
