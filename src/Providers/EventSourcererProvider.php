<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Providers;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\FetchEvents;
use Eventsourcerer\EventSourcererLaravel\DefaultEventHandler;
use Eventsourcerer\EventSourcererLaravel\EventHandler;
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
                    (int) config('eventsourcerer.port'),
                    config('eventsourcerer.applicationId')
                )
            );
        });

        $this->app->singleton(EventHandler::class, static function () {
            return new DefaultEventHandler();
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/eventsourcerer.php', 'eventsourcerer'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchEvents::class,
            ]);
        }
    }
}
