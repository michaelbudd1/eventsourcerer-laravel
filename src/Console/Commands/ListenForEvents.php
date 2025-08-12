<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Eventsourcerer\EventSourcererLaravel\EventHandler;
use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;

final class ListenForEvents extends Command
{
    public const string SIGNATURE = 'eventsourcerer:listen-for-events';
    protected $signature = self::SIGNATURE;
    protected $description = 'Listens for events';

    public function handle(Client $client, EventHandler $eventHandler): void
    {
        $client->connect()->listenForMessages($eventHandler->handle());
    }
}
