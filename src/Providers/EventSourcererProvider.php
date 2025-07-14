<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;

final class EventSourcererProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, static function () {
            return new Client(
                new Config(
                    config('eventsourcerer.host'),
                    config('eventsourcerer.url'),
                    config('eventsourcerer.port'),
                    config('eventsourcerer.applicationId')
                )
            );
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/eventsourcerer.php', 'eventsourcerer'
        );
    }
}
