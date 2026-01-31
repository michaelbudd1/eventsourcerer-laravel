<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Repository;

use Illuminate\Support\Facades\DB;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

final readonly class CacheWorkerEvents implements WorkerEvents
{
    public const string STORE_TABLE = 'eventsourcerer_queue';

    public function add(array $event): void
    {
        DB::table(self::STORE_TABLE)
          ->insert([
              'workerId' => $event['workerId'],
              'allSequence' => $event['allSequence'],
              'payload' => json_encode($event, JSON_THROW_ON_ERROR)
          ]);
    }

    public function remove(array $event): void
    {
        DB::table(self::STORE_TABLE)
          ->where('allSequence', $event['allSequence'])
          ->delete();
    }

    public function countFor(WorkerId $workerId): int
    {
        return DB::table(self::STORE_TABLE)
            ->where('workerId', $workerId->toString())
            ->count();
    }

    public function popFor(WorkerId $workerId): ?array
    {
        $row = DB::table(self::STORE_TABLE)
            ->select('payload')
            ->where('workerId', $workerId->toString())
            ->orderBy('allSequence')
            ->limit(1)
            ->first();

        if (null === $row) {
            return null;
        }

        $decoded = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR);

        DB::table(self::STORE_TABLE)->where('allSequence', $decoded['allSequence'])->delete();

        return $decoded;
    }

    public function allFor(WorkerId $workerId): array
    {
        return DB::table(self::STORE_TABLE)
            ->where('workerId', $workerId->toString())
            ->get()
            ->all();
    }
}
