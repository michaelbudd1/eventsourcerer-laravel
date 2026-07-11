<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\LaravelClient\Providers;

use PearTreeWeb\EventSourcerer\LaravelClient\Console\Commands\ListenForEvents;
use PearTreeWeb\EventSourcerer\LaravelClient\Console\Commands\Testing\ValidateWorkerSequencing;
use PearTreeWeb\EventSourcerer\LaravelClient\Console\Commands\WriteNewEvent;
use PearTreeWeb\EventSourcerer\LaravelClient\DefaultEventHandler;
use PearTreeWeb\EventSourcerer\LaravelClient\EventHandler;
use PearTreeWeb\EventSourcerer\LaravelClient\Queue\EventSourcererConnector;
use PearTreeWeb\EventSourcerer\LaravelClient\Repository\CacheWorkerEvents;
use PearTreeWeb\EventSourcerer\LaravelClient\Repository\WorkerEvents;
use Illuminate\Support\ServiceProvider;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationType;

final class EventSourcererProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, static function () {
            return new Client(
                new Config(
                    ApplicationType::Laravel,
                    config('eventsourcerer.host'),
                    config('eventsourcerer.url'),
                    (int) config('eventsourcerer.port'),
                    config('eventsourcerer.applicationId')
                ),
            );
        });

        $this->app->singleton(EventHandler::class, static fn () => new DefaultEventHandler());
        $this->app->singleton(WorkerEvents::class, static fn () => new CacheWorkerEvents());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/eventsourcerer.php' => config_path('eventsourcerer.php'),
        ]);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $manager = $this->app['queue'];
        $manager->addConnector('eventsourcerer', function() {
            return new EventSourcererConnector(
                $this->app->make(WorkerEvents::class),
                $this->app->make(Client::class),
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListenForEvents::class,
                ValidateWorkerSequencing::class,
                WriteNewEvent::class,
            ]);
        }
    }
}
