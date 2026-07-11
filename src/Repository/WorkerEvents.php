<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\LaravelClient\Repository;

use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

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

    public function allFor(WorkerId $workerId): array;
}
