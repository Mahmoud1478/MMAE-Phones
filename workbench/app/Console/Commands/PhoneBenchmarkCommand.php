<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\File;
use MMAE\Phones\CountryDetector;
use Workbench\App\Support\PhoneDataFactory;

/**
 * Benchmark the shipped {@see CountryDetector} over a large dataset — the
 * reference numbers for `detect()` and `detectFirst()` as they ship. Dev-only
 * harness (lives in the package workbench, run via `php vendor/bin/testbench`);
 * it is not shipped to consumers. Every run is appended to
 * `phone-benchmarks.jsonl` in the workbench storage so past numbers survive for
 * comparison.
 */
class PhoneBenchmarkCommand extends Command
{
    protected $signature = 'phones:benchmark
        {rows=1000000 : How many phone numbers to detect (ignored with --file)}
        {--valid=50 : Percentage of generated rows that are valid (0-100); the rest are invalid — bad key, wrong length, wrong provider, or garbage}
        {--file= : Read phones from a phones:dataset CSV instead of generating them}
        {--limit=0 : With --file, cap the rows read (0 = whole file)}
        {--chunk=50000 : Rows held in memory at once (a generator streams the rest)}
        {--no-save : Skip appending the result to the history file}';

    protected $description = 'Benchmark CountryDetector::detect() over a phones:dataset CSV or a generated valid/invalid mix across every country';

    public function handle(): int
    {
        $file = $this->option('file');
        $chunkSize = max(1, (int) $this->option('chunk'));

        if ($file !== null && $file !== '') {
            if (! is_file((string) $file)) {
                $this->error("Dataset not found: {$file} — generate one with `phones:dataset`.");

                return self::FAILURE;
            }
            $this->components->info(sprintf(
                'Streaming %s in chunks of %s…',
                basename((string) $file),
                number_format($chunkSize),
            ));
            $chunks = $this->readCsvChunks((string) $file, $chunkSize, (int) $this->option('limit'));
            $dataset = basename((string) $file);
        } else {
            $rows = max(1, (int) $this->argument('rows'));
            $validPercent = max(0, min(100, (int) $this->option('valid')));
            /** @var array<string, array<string, string>> $config */
            $config = config('phones', []);
            if ($config === []) {
                $this->error('config("phones") is empty — is the package provider registered?');

                return self::FAILURE;
            }
            $this->components->info(sprintf(
                'Generating %s phone numbers (%d%% valid) across every country…',
                number_format($rows),
                $validPercent,
            ));
            $chunks = $this->generateChunks($rows, $validPercent, $chunkSize, $config);
            $dataset = "generated-{$validPercent}pct";
        }

        // Accumulate detection time per candidate across chunks. The generator
        // keeps only one chunk resident, so memory stays flat no matter how big
        // the dataset is; CSV parsing / row generation happens outside the
        // measured closures, so the numbers time detection alone.
        $ms = ['detect' => 0.0, 'detectFirst' => 0.0];
        $total = 0;
        $valid = 0;
        $warmed = false;

        foreach ($chunks as [$rowsChunk, $validInChunk]) {
            if ($rowsChunk === []) {
                continue;
            }
            if (! $warmed) {
                CountryDetector::detect($rowsChunk[0]); // warm the compiled index
                $warmed = true;
            }
            $total += count($rowsChunk);
            $valid += $validInChunk;

            $ms['detect'] += Benchmark::measure(static function () use ($rowsChunk): void {
                foreach ($rowsChunk as $phone) {
                    CountryDetector::detect($phone);
                }
            }, 1);
            $ms['detectFirst'] += Benchmark::measure(static function () use ($rowsChunk): void {
                foreach ($rowsChunk as $phone) {
                    CountryDetector::detectFirst($phone);
                }
            }, 1);
        }

        if ($total === 0) {
            $this->error('No phone numbers to benchmark.');

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            '%s rows detected (%d%% valid, %d%% rejected).',
            number_format($total),
            (int) round($valid / $total * 100),
            (int) round(($total - $valid) / $total * 100),
        ));

        $results = [];
        foreach ($ms as $name => $elapsed) {
            $results[$name] = [
                'ms' => round($elapsed, 1),
                'per_row_us' => round($elapsed * 1000 / $total, 3),
                'rows_per_s' => (int) round($total / ($elapsed / 1000)),
            ];
        }

        $this->renderTable($total, $results);

        if (! $this->option('no-save')) {
            $this->persist($total, $dataset, $results);
        }

        return self::SUCCESS;
    }

    /**
     * Stream the phone column of a `phones:dataset` CSV (`phone,status,country_code`)
     * in chunks, so an arbitrarily large file is benchmarked with only one chunk
     * resident at a time. Blank phone cells are skipped. Each yield is
     * `[list<string> $phones, int $validCount]` for that chunk.
     *
     * @return \Generator<int, array{0: list<string>, 1: int}>
     */
    private function readCsvChunks(string $file, int $chunk, int $limit): \Generator
    {
        $fh = fopen($file, 'r');
        if ($fh === false) {
            $this->error("Cannot open {$file} for reading.");

            return;
        }

        fgetcsv($fh); // header
        $buffer = [];
        $valid = 0;
        $read = 0;
        while (($row = fgetcsv($fh)) !== false) {
            $phone = (string) ($row[0] ?? '');
            if ($phone === '') {
                continue;
            }
            $buffer[] = $phone;
            if (($row[1] ?? '') === 'valid') {
                $valid++;
            }
            $read++;
            if (count($buffer) >= $chunk) {
                yield [$buffer, $valid];
                $buffer = [];
                $valid = 0;
            }
            if ($limit > 0 && $read >= $limit) {
                break;
            }
        }
        fclose($fh);

        if ($buffer !== []) {
            yield [$buffer, $valid];
        }
    }

    /**
     * Generate N phones with the same {@see PhoneDataFactory} `phones:dataset`
     * uses — valid numbers from a random country, invalid ones split across every
     * failure mode (bad key, wrong length, wrong provider, garbage), valid/invalid
     * interleaved at random against $validPercent — yielding them in chunks so the
     * full set never lives in memory at once. Each yield is
     * `[list<string> $phones, int $validCount]`.
     *
     * @param  array<string, array<string, string>>  $config
     * @return \Generator<int, array{0: list<string>, 1: int}>
     */
    private function generateChunks(int $rows, int $validPercent, int $chunk, array $config): \Generator
    {
        $factory = new PhoneDataFactory($config);
        $buffer = [];
        $valid = 0;
        for ($i = 0; $i < $rows; $i++) {
            [$phone, $status] = $factory->row($validPercent);
            $buffer[] = $phone;
            if ($status === 'valid') {
                $valid++;
            }
            if (count($buffer) >= $chunk) {
                yield [$buffer, $valid];
                $buffer = [];
                $valid = 0;
            }
        }

        if ($buffer !== []) {
            yield [$buffer, $valid];
        }
    }

    /**
     * @param  array<string, array{ms: float, per_row_us: float, rows_per_s: int}>  $results
     */
    private function renderTable(int $rows, array $results): void
    {
        $this->newLine();
        $this->line(sprintf('  <fg=gray>rows:</> %s', number_format($rows)));
        $this->table(
            ['candidate', 'total (ms)', 'per row (µs)', 'rows/s'],
            collect($results)->map(static fn (array $r, string $name): array => [
                $name,
                number_format($r['ms'], 1),
                number_format($r['per_row_us'], 3),
                number_format($r['rows_per_s']),
            ])->values()->all(),
        );
    }

    /**
     * @param  array<string, array{ms: float, per_row_us: float, rows_per_s: int}>  $results
     */
    private function persist(int $rows, string $dataset, array $results): void
    {
        $record = [
            'at' => now()->toDateTimeString(),
            'rows' => $rows,
            'dataset' => $dataset,
            'php' => PHP_VERSION,
            'results' => $results,
        ];

        $path = storage_path('app/phone-benchmarks.jsonl');
        File::append($path, json_encode($record, JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->components->info('Saved to '.$path);
    }
}
