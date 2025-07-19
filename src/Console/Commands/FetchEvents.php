<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Eventsourcerer\EventSourcererLaravel\EventHandler;
use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;

final class FetchEvents extends Command
{
    protected $signature = 'eventsourcerer:fetch-events';
    protected $description = 'Fetches new events';

    public function handle(Client $client, EventHandler $eventHandler): void
    {
        $client->connect()->fetchMessages($eventHandler->handle());
    }
}
