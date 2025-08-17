<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class WriteNewEvent extends Command
{
    public const string SIGNATURE = 'eventsourcerer:write-new-event';

    protected $signature = self::SIGNATURE;

    protected $description = 'Writes new event';

    public function handle(Client $client): void
    {
        $client
            ->connect()
            ->writeNewEvent(
                StreamId::fromString('*'),
                Checkpoint::fromString($this->argument('streamCheckpoint')),
                Checkpoint::fromString($this->argument('allStreamCheckpoint'))
            );

        $this->output->success('Event removed from queue');
    }
}
