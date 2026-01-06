<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

final class ListenForEvents extends Command
{
    public const string SIGNATURE = 'eventsourcerer:listen-for-events';
    private const string EVENTS_CACHE_KEY = 'events';

    protected $signature = self::SIGNATURE;
    protected $description = 'Listens for events';

    public function handle(Client $client): void
    {
        $client->catchup(WorkerId::fromString('test'), self::handleNewEvents());
    }

    private static function handleNewEvents(): callable
    {
        return static function (array $decodedEvent): void {
            var_dump($decodedEvent); die;
//            $events = Cache::get(self::EVENTS_CACHE_KEY, []);
//
//            $events[$decodedEvent['allSequence']] = $decodedEvent;
//
//            Cache::set(self::EVENTS_CACHE_KEY, $events);
        };
    }
}
