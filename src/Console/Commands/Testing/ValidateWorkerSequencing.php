<?php

namespace Eventsourcerer\EventSourcererLaravel\Console\Commands\Testing;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('eventsourcerer:testing:validate-worker-sequencing')]
#[Description('Validates that each stream was processed by 1 worker and in sequence')]
final class ValidateWorkerSequencing extends Command
{
    public function __invoke(SymfonyStyle $style): int
    {
        $processed = [];

        $errorsFound = 0;

        foreach (self::logFiles($this->eventsourcererProjectDir) as $logFile) {
            $fh = fopen($this->eventsourcererProjectDir . '/var/log/' . $logFile, 'r');

            $lineNumber = 0;

            while (!feof($fh)) {
                $line = rtrim(fgets($fh), "\r\n");

                if ($line === false) {
                    break;
                }

                $lineNumber++;
                $parts = explode(' ', $line);

                if (!isset($parts[1])) {
                    break;
                }

                $stream = $parts[1];
                $sequence = (int)$parts[3];

                $processed[$stream][$sequence][] = ['file' => $logFile, 'line' => $lineNumber];

                if (1 !== count($processed[$stream][$sequence])) {
                    $style->warning(
                        sprintf(
                            'Stream %s sequence %d was processed several times (first: %s:%d, duplicate: %s:%d)',
                            $stream,
                            $sequence,
                            $processed[$stream][$sequence][0]['file'],
                            $processed[$stream][$sequence][0]['line'],
                            $logFile,
                            $lineNumber
                        )
                    );
                }

                $maxSequence = max(array_keys($processed[$stream]));

                if ($sequence < $maxSequence) {
                    $errorsFound++;

                    $style->warning(
                        sprintf(
                            'Stream %s sequence %d is not in correct order',
                            $stream,
                            $sequence
                        )
                    );
                }
            }
        }

        if (0 === $errorsFound) {
            $style->success('No errors found');
        } else {
            $style->error(sprintf('%d errors found', $errorsFound));
        }

        return Command::SUCCESS;
    }

    private static function logFiles(string $eventsourcererProjectDir): iterable
    {
        $logsDir = $eventsourcererProjectDir . '/var/log';

        foreach (scandir($logsDir) as $logFile) {
            if (preg_match('/worker-\d+\.log$/', $logFile)) {
                yield $logFile;
            }
        }
    }
}