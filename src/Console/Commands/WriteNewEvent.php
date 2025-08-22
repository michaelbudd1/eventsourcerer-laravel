<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands;

use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final class WriteNewEvent extends Command
{
    public const string SIGNATURE_PREFIX = 'eventsourcerer:write-new-event ';

    protected $signature = self::SIGNATURE_PREFIX . ' {streamId} {eventName} {eventVersion} {payload}';

    protected $description = 'Writes new event';

    public function handle(Client $client): void
    {
        $client
            ->connect()
            ->writeNewEvent(
                StreamId::fromString('*'),
                EventName::fromString($this->argument('eventName')),
                EventVersion::fromString($this->argument('eventVersion')),
                $this->argument('payload')
            );

        $this->output->success('New event written');
    }
}
