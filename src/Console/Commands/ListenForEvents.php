<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Eventsourcerer\EventSourcererLaravel\Repository\WorkerEvents;
use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

final class ListenForEvents extends Command
{
    public const string SIGNATURE = 'eventsourcerer:listen-for-events {worker}';

    protected $signature = self::SIGNATURE;
    protected $description = 'Listens for events';

    public function handle(Client $client, WorkerEvents $workerEvents): void
    {
        $client->catchup(WorkerId::fromString($this->argument('worker')), self::handleNewEvents($workerEvents));
    }

    private static function handleNewEvents(WorkerEvents $workerEvents): callable
    {
        return static function (array $decodedEvent) use ($workerEvents): void {
            $workerEvents->add($decodedEvent);
        };
    }
}
