<?php

declare(strict_types=1);

namespace Eventsourcerer\EventSourcererLaravel\Exception;

use Eventsourcerer\EventSourcererLaravel\Console\Commands\WriteNewEvent;

final class QueueCannotProcessJob extends \RuntimeException
{
    public static function becauseTheJobTypeIsInvalid(string $type): self
    {
        return new self(
            sprintf(
                'Cannot accept job with type "%s". This queue only accepts jobs with types "%s"',
                $type,
                implode(
                    ', ',
                    [
                        WriteNewEvent::class,
                    ]
                )
            )
        );
    }
}
