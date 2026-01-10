<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Eventsourcerer\EventSourcererLaravel\Repository\WorkerEvents;
use Illuminate\Database\Connectors\ConnectorInterface;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;

final readonly class EventSourcererConnector implements ConnectorInterface
{
    public function __construct(private WorkerEvents $workerEvents, private Client $client) {}

    /**
     * @param array{applicationId: string} $config
     */
    public function connect(array $config): EventSourcererQueue
    {
        return new EventSourcererQueue($this->workerEvents, $this->client);
    }
}
