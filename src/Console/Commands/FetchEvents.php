<?php

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;

class FetchEvents extends Command
{
    /**
     * @var string
     */
    protected $signature = 'eventsourcerer:fetch-events';

    /**
     * @var string
     */
    protected $description = 'Fetches new events';

    public function handle(Client $client): void
    {
        $client->fetchMessages(function ($message) {
            \Log::info('NEW: ' . json_encode($message));
        });
    }
}
