<?php

namespace Workbench\App\Console\Commands;

use Generator;
use Illuminate\Console\Command;
use MMAE\Phones\CountryDetector;

/**
 * Verify a generated phone dataset against the package's real
 * {@see CountryDetector} and write an annotated copy next to it.
 *
 * The input `phone,status,country_code` CSV is streamed in chunks (never loaded
 * whole), each row is re-checked, and the result is written to
 * `{filename}-verified.csv` in the same directory with two extra columns:
 *  - `detected` — pipe-joined country codes the detector resolved, and
 *  - `verdict`  — one of:
 *      - `ok`                — label agrees with the detector,
 *      - `bad_valid`         — a `valid` row the detector rejects/misattributes,
 *      - `invalid_with_code` — an `invalid` row that still carries a country code,
 *      - `cross_country`     — an `invalid` row that is valid for another country
 *                              (noise: generation only checks each number against
 *                              its own country).
 *
 * Each chunk is flushed to disk before the next is read, so memory stays flat
 * regardless of file size.
 */
class VerifyPhonesDatasetCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'phones:verify
        {file : Path to the CSV to verify}
        {--chunk=20000 : Rows to buffer before flushing to disk}
        {--limit=0 : Max data rows to check (0 = all)}';

    /**
     * @var string
     */
    protected $description = 'Verify a phone dataset CSV against CountryDetector into {filename}-verified.csv';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = max(0, (int) $this->option('limit'));

        $out = $this->verifiedPath($file);

        CountryDetector::flush();

        $in = fopen($file, 'r');
        $writer = fopen($out, 'w');
        if ($in === false || $writer === false) {
            $this->error('Cannot open input or output file.');

            return self::FAILURE;
        }
        fgetcsv($in, 0, ',', '"', ''); // skip header
        fwrite($writer, "phone,status,country_code,detected,verdict\r\n");

        $this->components->info("Verifying {$file}");
        $this->components->twoColumnDetail('Output', $out);

        $counts = ['ok' => 0, 'bad_valid' => 0, 'invalid_with_code' => 0, 'cross_country' => 0];
        $validTotal = 0;
        $total = 0;
        $buffer = '';
        $inChunk = 0;

        foreach ($this->rows($in, $limit) as $record) {
            [$phone, $status, $code] = array_pad($record, 3, '');
            $detected = CountryDetector::detect($phone);
            $verdict = $this->verdict($status, $code, $detected);

            $counts[$verdict]++;
            if ($status === 'valid') {
                $validTotal++;
            }
            $total++;
            $buffer .= implode(',', [$phone, $status, $code, implode('|', $detected), $verdict])."\r\n";

            if (++$inChunk >= $chunkSize) {
                fwrite($writer, $buffer);
                fflush($writer);
                $buffer = '';
                $inChunk = 0;
                $this->components->task("Flushed {$total} rows", fn (): bool => true);
            }
        }

        if ($buffer !== '') {
            fwrite($writer, $buffer);
            fflush($writer);
        }

        fclose($in);
        fclose($writer);

        $matchedCountry = $validTotal - $counts['bad_valid']; // valid rows whose labelled code was in detected
        $broke = $counts['bad_valid'] + $counts['invalid_with_code']; // hard label failures

        $this->renderStats($total, [
            'right' => $counts['ok'],
            'wrong' => $total - $counts['ok'],
            'matched' => $matchedCountry,
            'validTotal' => $validTotal,
            'cross' => $counts['cross_country'],
            'broke' => $broke,
        ]);

        if ($counts['bad_valid'] > 0 || $counts['invalid_with_code'] > 0) {
            $this->components->error('Dataset has label errors — see verdict column.');

            return self::FAILURE;
        }

        $this->components->info('All labels consistent with CountryDetector.');

        return self::SUCCESS;
    }

    /**
     * Stream data rows from the open handle, stopping at $limit (0 = no limit).
     *
     * @param  resource  $handle
     * @return Generator<int, array<int, string>>
     */
    private function rows($handle, int $limit): Generator
    {
        $read = 0;
        while (($record = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            yield $record;
            if (++$read === $limit) {
                return;
            }
        }
    }

    /**
     * Render the verification stats with percentages inside a bordered panel.
     *
     * @param  array{right:int, wrong:int, matched:int, validTotal:int, cross:int, broke:int}  $s
     */
    private function renderStats(int $total, array $s): void
    {
        $inner = 52;
        $barWidth = $inner - 4;
        $pct = fn (int $n, int $of): float => $of > 0 ? $n / $of * 100 : 0.0;

        $rightPct = $pct($s['right'], $total);
        $rightCells = (int) round($rightPct / 100 * $barWidth);
        $validBar = str_repeat('█', $rightCells).str_repeat('░', $barWidth - $rightCells);

        // Pad to $inner *visible* columns (mb_strlen): the glyphs below are
        // multibyte but one column wide, so byte-based str_pad would misalign the
        // right border. Colour tags wrap the whole padded line, never the width.
        $pad = fn (string $str): string => $str.str_repeat(' ', max(0, $inner - mb_strlen($str)));
        $line = fn (string $body, string $colour = ''): string => '  <fg=cyan>│</>'
            .($colour === '' ? $pad($body) : "<fg={$colour}>".$pad($body).'</>')
            .'<fg=cyan>│</>';
        $stat = fn (string $marker, string $label, int $count, float $percent): string => sprintf(
            '  %s %-18s %13s  %6.2f%%', $marker, $label, number_format($count), $percent
        );

        $this->newLine();
        $this->line('  <fg=cyan>┌'.str_repeat('─', $inner).'┐</>');
        $this->line($line('  Verification result'));
        $this->line('  <fg=cyan>├'.str_repeat('─', $inner).'┤</>');
        $this->line($line('  Total '.str_pad(number_format($total), $inner - 8, ' ', STR_PAD_LEFT)));
        $this->line($line($stat('✔', 'Right', $s['right'], $rightPct), 'green'));
        $this->line($line($stat('✘', 'Wrong', $s['wrong'], $pct($s['wrong'], $total)), 'red'));
        $this->line('  <fg=cyan>├'.str_repeat('─', $inner).'┤</>');
        $this->line($line($stat('◉', 'Country matched', $s['matched'], $pct($s['matched'], $s['validTotal'])), 'green'));
        $this->line($line($stat('~', 'Cross-country', $s['cross'], $pct($s['cross'], $total)), 'yellow'));
        $this->line($line($stat('☠', 'Totally broke', $s['broke'], $pct($s['broke'], $total)), 'red'));
        $this->line('  <fg=cyan>│</><fg=green>'.$pad('  '.$validBar).'</><fg=cyan>│</>');
        $this->line('  <fg=cyan>└'.str_repeat('─', $inner).'┘</>');

        $this->line('  <fg=gray>Country matched % is of valid rows; Cross-country = invalid numbers valid elsewhere.</>');
    }

    /**
     * Classify a row against the detector's result.
     *
     * @param  list<string>  $detected
     */
    private function verdict(string $status, string $code, array $detected): string
    {
        if ($status === 'valid') {
            return in_array($code, $detected, true) ? 'ok' : 'bad_valid';
        }

        if ($code !== '') {
            return 'invalid_with_code';
        }

        return $detected === [] ? 'ok' : 'cross_country';
    }

    /**
     * `dir/name.csv` -> `dir/name-verified.csv` (keeps any extension).
     */
    private function verifiedPath(string $file): string
    {
        $dir = dirname($file);
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $name = pathinfo($file, PATHINFO_FILENAME);
        $suffix = $ext === '' ? '' : ".{$ext}";

        return $dir.DIRECTORY_SEPARATOR."{$name}-verified{$suffix}";
    }
}
