<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Repository;

use PearTreeWebLtd\EventSourcererMessageUtilities\Model\WorkerId;

interface WorkerEvents
{
    /**
     * @param array{workerId: string} $event
     */
    public function add(array $event): void;

    /**
     * @param array{workerId: string} $event
     */
    public function remove(array $event): void;

    public function countFor(WorkerId $workerId): int;

    public function popFor(WorkerId $workerId): ?array;
}
