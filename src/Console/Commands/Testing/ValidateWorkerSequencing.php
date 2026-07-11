<?php

namespace PearTreeWeb\EventSourcerer\LaravelClient\Console\Commands\Testing;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('eventsourcerer:testing:validate-worker-sequencing')]
#[Description('Validates that each stream was processed by 1 worker and in sequence')]
final class ValidateWorkerSequencing extends Command
{
    public function __invoke(): int
    {
        $processed = [];
        $errorsFound = 0;
        $storagePath = storage_path();

        foreach (self::logFiles($storagePath) as $logFile) {
            $fh = fopen(
                sprintf(
                    '%s/%s',
                    $storagePath,
                    $logFile,
                ),
                'r',
            );

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
                    $this->warn(
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

                    $this->warn(
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
            $this->line('No errors found');
        } else {
            $this->warn(sprintf('%d errors found', $errorsFound));
        }

        return Command::SUCCESS;
    }

    private static function logFiles(string $storagePath): iterable
    {
        foreach (scandir($storagePath) as $logFile) {
            if (preg_match('/worker\-(.)+\.log$/', $logFile)) {
                yield $logFile;
            }
        }
    }
}