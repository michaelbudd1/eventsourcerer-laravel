<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Eventsourcerer\EventSourcererLaravel\EventHandler;
use Illuminate\Database\Connectors\ConnectorInterface;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;

final readonly class EventSourcererConnector implements ConnectorInterface
{
    public function __construct(
        private Client $client,
        private EventHandler $eventHandler
    ) {}

    /**
     * @param array{applicationId: string} $config
     */
    public function connect(array $config): EventSourcererQueue
    {
        return new EventSourcererQueue(
            $this->client,
            ApplicationId::fromString($config['applicationId']),
            $this->eventHandler
        );
    }
}
