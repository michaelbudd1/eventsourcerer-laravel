<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Providers;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\ListenForEvents;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\RemoveEventFromQueue;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent;
use Eventsourcerer\EventSourcererLaravel\DefaultEventHandler;
use Eventsourcerer\EventSourcererLaravel\EventHandler;
use Eventsourcerer\EventSourcererLaravel\Queue\EventSourcererConnector;
use Eventsourcerer\EventSourcererLaravel\Repository\CacheWorkerEvents;
use Eventsourcerer\EventSourcererLaravel\Repository\WorkerEvents;
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

        $manager = $this->app['queue'];
        $manager->addConnector('eventsourcerer', function() {
            return new EventSourcererConnector(
                $this->app->make(WorkerEvents::class)
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListenForEvents::class,
                RemoveEventFromQueue::class,
                WriteNewEvent::class,
            ]);
        }
    }
}
