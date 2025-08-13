<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class RemoveEventFromQueue extends Command
{
    public const string SIGNATURE_PREFIX = 'eventsourcerer:remove-event-from-queue';
    protected $signature = self::SIGNATURE_PREFIX . ' {streamCheckpoint} {allStreamCheckpoint}';
    protected $description = 'Remove event from queue';

    public function handle(Client $client): void
    {
        $client
            ->connect()
            ->acknowledgeEvent(
                StreamId::fromString('*'),
                Checkpoint::fromString($this->argument('streamCheckpoint')),
                Checkpoint::fromString($this->argument('allStreamCheckpoint'))
            );

        $client->disconnect();

        $this->output->success('Event removed from queue');
    }
}
