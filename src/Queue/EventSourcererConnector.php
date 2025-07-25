<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Database\Connectors\ConnectorInterface;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;

final readonly class EventSourcererConnector implements ConnectorInterface
{
    public function __construct(private Client $client) {}

    public function connect(array $config): EventSourcererQueue
    {
        return new EventSourcererQueue($this->client);
    }
}
