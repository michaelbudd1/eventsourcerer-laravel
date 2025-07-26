<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel;

use Eventsourcerer\EventSourcererLaravel\Queue\NewEventJob;

final readonly class DefaultEventHandler implements EventHandler
{
    public function handle(): callable
    {
        return static function (array $event): void {
//            $job = (new NewEventJob($event))->onConnection('eventsourcerer');

            dispatch(new NewEventJob($event));
        };
    }
}
