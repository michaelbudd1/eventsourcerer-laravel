<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Repository;

use Illuminate\Support\Facades\Cache;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

final readonly class CacheWorkerEvents implements WorkerEvents
{
    private const string EVENTS_CACHE_KEY = 'events';

    public function add(array $event): void
    {
        $events = Cache::get(self::EVENTS_CACHE_KEY, []);

        $events[$event['allSequence']] = $event;

        Cache::set(self::EVENTS_CACHE_KEY, $events);
    }

    public function remove(array $event): void
    {
        $events = Cache::get(self::EVENTS_CACHE_KEY, []);

        unset($events[$event['allSequence']]);

        Cache::set(self::EVENTS_CACHE_KEY, $events);
    }

    public function countFor(WorkerId $workerId): int
    {
        return count(Cache::get(self::EVENTS_CACHE_KEY, []));
    }
}
