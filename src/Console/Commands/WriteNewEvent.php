<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\LaravelClient\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Model\EventVersion;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

#[Signature(self::SIGNATURE_PREFIX . ' {streamId} {eventName} {eventVersion} {payload}')]
#[Description('Writes new event')]
final class WriteNewEvent extends Command
{
    public const string SIGNATURE_PREFIX = 'eventsourcerer:write-new-event ';

    public function handle(Client $client): void
    {
        $client
            ->writeNewEvent(
                StreamId::fromString('*'),
                EventName::fromString($this->argument('eventName')),
                EventVersion::fromString($this->argument('eventVersion')),
                json_decode($this->argument('payload'), true, 512, JSON_THROW_ON_ERROR)
            );

        $this->output->success('New event written');
    }
}
