<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Providers;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\ListenForEvents;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\RemoveEventFromQueue;
use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent;
use Eventsourcerer\EventSourcererLaravel\DefaultEventHandler;
use Eventsourcerer\EventSourcererLaravel\EventHandler;
use Eventsourcerer\EventSourcererLaravel\Queue\EventSourcererConnector;
use Illuminate\Support\ServiceProvider;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\CachedAvailableEvents;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationType;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class EventSourcererProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, static function () {
            $cache = new FilesystemAdapter(
                directory: config('eventsourcerer.cache.path')
            );

            return new Client(
                new Config(
                    ApplicationType::Laravel,
                    config('eventsourcerer.host'),
                    config('eventsourcerer.url'),
                    (int) config('eventsourcerer.port'),
                    config('eventsourcerer.applicationId')
                ),
                new CachedAvailableEvents($cache)
            );
        });

        $this->app->singleton(EventHandler::class, static function () {
            return new DefaultEventHandler();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/eventsourcerer.php' => config_path('eventsourcerer.php'),
        ]);

        $manager = $this->app['queue'];
        $manager->addConnector('eventsourcerer', function() {
            return new EventSourcererConnector(
                $this->app->make(Client::class)
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
