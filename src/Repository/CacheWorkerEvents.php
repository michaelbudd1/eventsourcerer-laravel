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
        $allEvents = Cache::get(self::EVENTS_CACHE_KEY, []);
        $events = $allEvents[$workerId->toString()] ?? [];
        $keys = array_keys($events);

        if (empty($keys)) {
            return null;
        }

        $lowest = min($keys);
        $event = $events[$lowest];

        if (null !== $event) {
            unset($allEvents[$workerId->toString()][$lowest]);

            Cache::set(self::EVENTS_CACHE_KEY, $allEvents);
        }

        return $event;
    }

    public function allFor(WorkerId $workerId): array
    {
        return Cache::get(self::EVENTS_CACHE_KEY, [])[$workerId->toString()] ?? [];
    }
}
