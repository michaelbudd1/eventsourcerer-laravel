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

        $events[$event['workerId']][$event['allSequence']] = $event;

        Cache::set(self::EVENTS_CACHE_KEY, $events);
    }

    public function remove(array $event): void
    {
        $events = Cache::get(self::EVENTS_CACHE_KEY, []);

        unset($events[$event['workerId']][$event['allSequence']]);

        Cache::set(self::EVENTS_CACHE_KEY, $events);
    }

    public function countFor(WorkerId $workerId): int
    {
        return count(Cache::get(self::EVENTS_CACHE_KEY, [])[$workerId->toString()] ?? []);
    }

    public function popFor(WorkerId $workerId): ?array
    {
        $events = Cache::get(self::EVENTS_CACHE_KEY, [])[$workerId->toString()] ?? [];

        $reversed = array_reverse($events);

        $event = array_pop($reversed);

        if (null !== $event) {
            unset($events[$workerId->toString()][$event['allSequence']]);

            Cache::set(self::EVENTS_CACHE_KEY, $events);
        }

        return $event;
    }
}
