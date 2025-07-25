<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Queue;

use Illuminate\Contracts\Queue\Queue;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;

final readonly class EventSourcererQueue implements Queue
{
    public function __construct(private Client $client) {}

    public function size($queue = null)
    {
        // TODO: Implement size() method.
    }

    public function push($job, $data = '', $queue = null)
    {
        // TODO: Implement push() method.
    }

    public function pushOn($queue, $job, $data = '')
    {
        // TODO: Implement pushOn() method.
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        // TODO: Implement pushRaw() method.
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        // TODO: Implement later() method.
    }

    public function laterOn($queue, $delay, $job, $data = '')
    {
        // TODO: Implement laterOn() method.
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        // TODO: Implement bulk() method.
    }

    public function pop($queue = null)
    {
        return $this->client->fetchOneMessage(
            ApplicationId::fromString('87fe0af1-c27f-53ac-ba05-6508930e17e4')
        );
    }

    public function getConnectionName()
    {
        // TODO: Implement getConnectionName() method.
    }

    public function setConnectionName($name)
    {
        // TODO: Implement setConnectionName() method.
    }

    public function __call(string $name, array $arguments)
    {
    }
}
