<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel;

final readonly class DefaultEventHandler implements EventHandler
{
    public function handle(): callable
    {
        return static function (array $event): void {
            echo json_encode($event, JSON_THROW_ON_ERROR);
        };
    }
}
