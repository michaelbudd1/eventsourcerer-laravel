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

    public function handle(): void
    {
        $client = (
            new Client(
                new Config(
                    '0.0.0.0',
                    'https://eventsourcerer.docker.localhost',
                    1984,
                    '40e71a4d-7a86-56c7-b51e-c4053523f28f'
                )
            )
        )->connect();

        $client->fetchMessages(function ($message) {
            \Log::info(json_encode($message));
        });
    }
}
