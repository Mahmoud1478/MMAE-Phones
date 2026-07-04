<?php

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Workbench\App\Support\PhoneDataFactory;

/**
 * Generate a shuffled CSV of phone numbers (valid + invalid), mirroring the
 * dataset under `datasets/`.
 *
 * Rows are `phone,status,country_code`; valid/invalid are randomly interleaved
 * as they are produced, so the output is already shuffled. Pass `--shuffle` for
 * an extra in-memory Fisher-Yates pass (loads the whole file into memory — only
 * use it for row counts that comfortably fit).
 */
class GeneratePhonesDatasetCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'phones:dataset
        {rows=1000000 : Number of data rows to generate}
        {--out= : Output CSV path (default: phones-dataset-{rows}.csv in the cwd)}
        {--valid=50 : Percentage of rows that should be valid (0-100)}
        {--shuffle : Extra in-memory shuffle after generating (memory heavy)}';

    /**
     * @var string
     */
    protected $description = 'Generate a shuffled CSV of valid/invalid phone numbers';

    public function handle(): int
    {
        $rows = max(1, (int) $this->argument('rows'));
        $validPercent = max(0, min(100, (int) $this->option('valid')));
        $out = $this->option('out') ?: getcwd().DIRECTORY_SEPARATOR."phones-dataset-{$rows}.csv";

        /** @var array<string, array<string, string>> $config */
        $config = config('phones', []);
        if ($config === []) {
            $this->error('config("phones") is empty — is the package provider registered?');

            return self::FAILURE;
        }

        $factory = new PhoneDataFactory($config);

        $fh = fopen($out, 'w');
        if ($fh === false) {
            $this->error("Cannot open {$out} for writing.");

            return self::FAILURE;
        }

        $this->components->info("Generating {$rows} rows ({$validPercent}% valid) -> {$out}");
        $bar = $this->output->createProgressBar($rows);
        $bar->start();

        fwrite($fh, "phone,status,country_code\r\n");

        $valid = 0;
        $buf = '';
        for ($i = 0; $i < $rows; $i++) {
            [$phone, $status, $code] = $factory->row($validPercent);
            if ($status === 'valid') {
                $valid++;
            }
            $buf .= "{$phone},{$status},{$code}\r\n";
            if (($i % 20000) === 0 && $i > 0) {
                fwrite($fh, $buf);
                $buf = '';
                $bar->advance(20000);
            }
        }
        fwrite($fh, $buf);
        fclose($fh);
        $bar->finish();
        $this->newLine(2);

        if ($this->option('shuffle')) {
            $this->shuffleFile($out);
        }

        $this->components->info('Done: '.number_format($rows).' rows written.');
        $this->renderStats($rows, $valid, $rows - $valid);

        return self::SUCCESS;
    }

    /**
     * Render a valid/invalid breakdown with percentages inside a bordered panel.
     */
    private function renderStats(int $total, int $valid, int $invalid): void
    {
        $validPct = $total > 0 ? $valid / $total * 100 : 0.0;
        $invalidPct = $total > 0 ? $invalid / $total * 100 : 0.0;

        $inner = 44;
        $barWidth = $inner - 4;
        $validCells = $total > 0 ? (int) round($validPct / 100 * $barWidth) : 0;

        // Pad to $inner *visible* columns (mb_strlen) — the █ glyph is multibyte
        // but one column wide, so byte-based str_pad would misalign the border.
        $pad = fn (string $s): string => $s.str_repeat(' ', max(0, $inner - mb_strlen($s)));
        $row = fn (string $marker, string $label, int $count, float $pct): string => $pad(
            sprintf('  %s %-8s %13s  %6.2f%%', $marker, $label, number_format($count), $pct)
        );

        $top = '┌'.str_repeat('─', $inner).'┐';
        $sep = '├'.str_repeat('─', $inner).'┤';
        $bottom = '└'.str_repeat('─', $inner).'┘';
        $title = $pad('  Dataset breakdown');
        $totalLine = $pad('  Total '.str_pad(number_format($total), $inner - 8, ' ', STR_PAD_LEFT));
        $validBar = str_repeat('█', $validCells);
        $invalidBar = str_repeat('░', $barWidth - $validCells);

        $this->newLine();
        $this->line("  <fg=cyan>{$top}</>");
        $this->line('  <fg=cyan>│</><options=bold>'.$title.'</><fg=cyan>│</>');
        $this->line("  <fg=cyan>{$sep}</>");
        $this->line('  <fg=cyan>│</>'.$totalLine.'<fg=cyan>│</>');
        $this->line('  <fg=cyan>│</><fg=green>'.$row('●', 'Valid', $valid, $validPct).'</><fg=cyan>│</>');
        $this->line('  <fg=cyan>│</><fg=yellow>'.$row('○', 'Invalid', $invalid, $invalidPct).'</><fg=cyan>│</>');
        $this->line('  <fg=cyan>│</>  <fg=green>'.$validBar.'</><fg=yellow>'.$invalidBar.'</>'.str_repeat(' ', $inner - 2 - $barWidth).'<fg=cyan>│</>');
        $this->line("  <fg=cyan>{$bottom}</>");
    }

    /**
     * In-memory Fisher-Yates shuffle of the data rows (header stays first).
     */
    private function shuffleFile(string $path): void
    {
        $this->components->task('Shuffling in memory', function () use ($path): bool {
            $prev = ini_set('memory_limit', '-1');
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $header = array_shift($lines);

            for ($i = count($lines) - 1; $i > 0; $i--) {
                $j = mt_rand(0, $i);
                [$lines[$i], $lines[$j]] = [$lines[$j], $lines[$i]];
            }

            $fh = fopen($path, 'w');
            fwrite($fh, $header."\r\n");
            $buf = '';
            $n = 0;
            foreach ($lines as $line) {
                $buf .= $line."\r\n";
                if ((++$n % 50000) === 0) {
                    fwrite($fh, $buf);
                    $buf = '';
                }
            }
            fwrite($fh, $buf);
            fclose($fh);

            if ($prev !== false) {
                ini_set('memory_limit', $prev);
            }

            return true;
        });
    }
}
