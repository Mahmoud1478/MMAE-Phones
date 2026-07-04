<?php

declare(strict_types=1);

namespace MMAE\Phones\Support;

use MMAE\Phones\Commands\BuildLookupCommand;
use MMAE\Phones\CountryDetector;

/**
 * Compiles a `phones` schema into the length-first detection index that
 * {@see CountryDetector} walks.
 *
 * Build-time only: {@see BuildLookupCommand} calls {@see compile()} to bake
 * `config/phone-lookup.php`, and {@see CountryDetector::detect()} loads that
 * file directly — none of this runs at runtime.
 */
final class LookupCompiler
{
    /**
     * Compile a `phones` schema straight into the length-first index baked into
     * `config/phone-lookup.php` and loaded by {@see CountryDetector::detect()}.
     *
     * @param  array<string, array<string, string>>  $countries
     * @return array<int, mixed>
     */
    public static function compile(array $countries): array
    {
        return self::build(self::compileIndex($countries)['index']);
    }

    /**
     * Distribute the flat dialing-code index into a length-first index: the top
     * key is a total (dialing code + subscriber) length, and each value is a
     * dialing-code trie of only the countries whose numbers are exactly that long.
     * A variable-length pattern (e.g. `\d{6,7}`) lands in every total it can
     * produce, so leaves need no length gate and an impossible length is a single
     * top-level miss.
     *
     * @param  array<int|string, list<array{0: string, 1: list<array{0: int, 1: string}>, 2: int, 3: int}>>  $flat
     * @return array<int, mixed>
     */
    private static function build(array $flat): array
    {
        $index = [];
        foreach ($flat as $key => $buckets) {
            $key = (string) $key;
            $keyLength = strlen($key);
            $digits = $keyLength === 0 ? [] : str_split($key);

            // Shared dialing codes (>= 2 patterns, e.g. +1 NANP) get the
            // exact-provider hash; a unique code stays a single `$` regex.
            $shared = count($buckets) > 1;
            foreach ($buckets as [$pattern, $countries, $bucketMin, $bucketMax]) {
                $exact = $shared ? self::exactProvider($pattern) : null;
                if ($exact !== null) {
                    // fixed provider width + fixed `\d{n}` => a single total length
                    [$width, $providers] = $exact;
                    $total = $keyLength + $bucketMin;
                    foreach ($providers as $provider) {
                        $index[$total] = self::attachExact($index[$total] ?? [], $digits, $width, $provider, $countries);
                    }

                    continue;
                }

                $leaf = [$pattern, $countries];
                for ($subscriber = $bucketMin; $subscriber <= $bucketMax; $subscriber++) {
                    $total = $keyLength + $subscriber;
                    $index[$total] = self::attach($index[$total] ?? [], $digits, $leaf);
                }
            }
        }

        return $index;
    }

    /**
     * Insert one exact-provider leaf under the dialing-code path $digits, merging
     * its countries into `#` => width => provider => [[ordinal, code], ...].
     *
     * @param  array<int|string, mixed>  $node
     * @param  list<string>  $digits
     * @param  list<array{0: int, 1: string}>  $countries
     * @return array<int|string, mixed>
     */
    private static function attachExact(array $node, array $digits, int $width, string $provider, array $countries): array
    {
        if ($digits === []) {
            $map = $node['#'] ?? [];
            if (! is_array($map)) {
                $map = [];
            }
            $byWidth = $map[$width] ?? [];
            if (! is_array($byWidth)) {
                $byWidth = [];
            }
            $bucket = $byWidth[$provider] ?? [];
            if (! is_array($bucket)) {
                $bucket = [];
            }
            foreach ($countries as $country) {
                $bucket[] = $country;
            }
            $byWidth[$provider] = $bucket;
            $map[$width] = $byWidth;
            $node['#'] = $map;

            return $node;
        }

        $digit = $digits[0];
        $child = $node[$digit] ?? [];
        if (! is_array($child)) {
            $child = [];
        }
        $node[$digit] = self::attachExact($child, array_slice($digits, 1), $width, $provider, $countries);

        return $node;
    }

    /**
     * Insert one leaf under the dialing-code path $digits, creating trie nodes as
     * needed, and return the updated node.
     *
     * @param  array<int|string, mixed>  $node
     * @param  list<string>  $digits
     * @param  array{0: string, 1: list<array{0: int, 1: string}>}  $leaf
     * @return array<int|string, mixed>
     */
    private static function attach(array $node, array $digits, array $leaf): array
    {
        if ($digits === []) {
            $bucket = $node['$'] ?? [];
            if (! is_array($bucket)) {
                $bucket = [];
            }
            $bucket[] = $leaf;
            $node['$'] = $bucket;

            return $node;
        }

        $digit = $digits[0];
        $child = $node[$digit] ?? [];
        if (! is_array($child)) {
            $child = [];
        }
        $node[$digit] = self::attach($child, array_slice($digits, 1), $leaf);

        return $node;
    }

    /**
     * If $regex is a fixed-width literal-prefix pattern — provider is an
     * alternation of equal-length digit runs (`787|939`, `868`, `5`) and the
     * subscriber is a fixed `\d{n}` — return its `[width, providers]`; otherwise
     * null. Only these resolve via a hash lookup on the leading digits instead of
     * a `preg_match`. Anything with a class, `\d`, or a `{m,n}` range in the
     * provider (`\d{3}`, `[5-7][0-9]`, `9[1-5]`) stays a regex bucket.
     *
     * @return array{0: int, 1: list<string>}|null
     */
    private static function exactProvider(string $regex): ?array
    {
        if (! preg_match('#^/\^\(\?<provider>(.+?)\)\(\?<digits>\\\\d\{\d+\}\)\$/$#', $regex, $match)) {
            return null;
        }

        $providers = explode('|', $match[1]);
        $width = strlen($providers[0]);
        foreach ($providers as $provider) {
            if ($provider === '' || ! ctype_digit($provider) || strlen($provider) !== $width) {
                return null;
            }
        }

        return [$width, $providers];
    }

    /**
     * Compile a `phones` schema into the flat dialing-code index consumed by
     * {@see build()} and (via {@see compile()}) baked into
     * `config/phone-lookup.php`.
     *
     * Countries are grouped by dialing code, then — within each code — by an
     * identical anchored subscriber regex, so a shared pattern (e.g. every NANP
     * territory using `\d{3}\d{7}`) is stored and tested only once while still
     * mapping to every country that uses it. Config order is preserved via the
     * stored ordinal. The dialing code (`key`) is required and the national
     * trunk (`local_key`) is never accepted — that keeps detection to true
     * international numbers.
     *
     * @param  array<string, array<string, string>>  $countries
     * @return array{
     *     max_key_length: int,
     *     index: array<int|string, list<array{0: string, 1: list<array{0: int, 1: string}>, 2: int, 3: int}>>
     * }
     */
    public static function compileIndex(array $countries): array
    {
        /** @var array<int|string, array<string, list<array{0: int, 1: string}>>> $grouped */
        $grouped = [];
        $maxKeyLength = 0;
        $ordinal = 0;
        foreach ($countries as $code => $country) {
            $position = $ordinal++;
            $key = $country['key'] ?? '';
            $pattern = $country['pattern'] ?? '';
            if ($key === '' || $pattern === '') {
                continue;
            }

            // group by the compiled regex; identical patterns collapse to one entry
            $grouped[$key]["/^{$pattern}$/"][] = [$position, (string) $code];
            $maxKeyLength = max($maxKeyLength, strlen($key));
        }

        $index = [];
        foreach ($grouped as $key => $patterns) {
            $bucket = [];
            foreach ($patterns as $regex => $countryList) {
                [$minLength, $maxLength] = self::subscriberLength($regex);
                $bucket[] = [$regex, $countryList, $minLength, $maxLength];
            }
            $index[$key] = $bucket;
        }

        return ['max_key_length' => $maxKeyLength, 'index' => $index];
    }

    /**
     * The [min, max] subscriber length an anchored detection regex accepts, so a
     * variable-length pattern can be spread across exactly the total lengths it
     * can produce. Handles the schema's pattern vocabulary — named or
     * non-capturing groups, character classes, `\d`-style escapes, literals,
     * top-level alternation, and `{n}` / `{m,n}` quantifiers (no `+`/`*`/`?` or
     * nested groups occur).
     *
     * Nearly every schema pattern is the canonical
     * `(?<provider>PROV)(?<digits>\d{n})` (or `\d{n,m}`) shape, so a fast path
     * pulls the digit count arithmetically and parses only the small, group-free
     * PROV. Anything else falls through to the full {@see rangeOfAlternation()}
     * walk.
     *
     * @return array{0: int, 1: int}
     */
    private static function subscriberLength(string $regex): array
    {
        if (str_starts_with($regex, '/^')) {
            $regex = substr($regex, 2);
        }
        if (str_ends_with($regex, '$/')) {
            $regex = substr($regex, 0, -2);
        }

        if (preg_match('#^\(\?<provider>(.+)\)\(\?<digits>\\\\d\{(\d+)(?:,(\d+))?\}\)$#', $regex, $match)) {
            // PROV holds no nested groups — only classes/escapes/literals.
            [$providerMin, $providerMax] = self::rangeOfAlternation($match[1]);
            $digitsMin = (int) $match[2];
            $digitsMax = ($match[3] ?? '') !== '' ? (int) $match[3] : $digitsMin;

            return [$providerMin + $digitsMin, $providerMax + $digitsMax];
        }

        return self::rangeOfAlternation($regex);
    }

    /**
     * Length range of an alternation: min/max across its top-level branches.
     *
     * @return array{0: int, 1: int}
     */
    private static function rangeOfAlternation(string $expr): array
    {
        $min = PHP_INT_MAX;
        $max = 0;
        foreach (self::splitTopLevel($expr, '|') as $branch) {
            [$branchMin, $branchMax] = self::rangeOfConcatenation($branch);
            $min = min($min, $branchMin);
            $max = max($max, $branchMax);
        }

        return [$min === PHP_INT_MAX ? 0 : $min, $max];
    }

    /**
     * Length range of a concatenation: sum of each atom's range, where an atom is
     * a group (recursed), a character class, an escape, or a literal, optionally
     * scaled by a following `{n}` / `{m,n}` quantifier.
     *
     * @return array{0: int, 1: int}
     */
    private static function rangeOfConcatenation(string $expr): array
    {
        $min = 0;
        $max = 0;
        $index = 0;
        $length = strlen($expr);
        while ($index < $length) {
            $char = $expr[$index];

            if ($char === '(') {
                $close = self::matchingParen($expr, $index);
                $inner = substr($expr, $index + 1, $close - $index - 1);
                $stripped = preg_replace('/^\?<[A-Za-z_]\w*>|^\?:/', '', $inner);
                [$atomMin, $atomMax] = self::rangeOfAlternation($stripped ?? $inner);
                $index = $close + 1;
            } elseif ($char === '[') {
                $atomMin = $atomMax = 1;
                $index = strpos($expr, ']', $index + 1) + 1;
            } elseif ($char === '\\') {
                $atomMin = $atomMax = 1;
                $index += 2;
            } else {
                $atomMin = $atomMax = 1;
                $index++;
            }

            if ($index < $length && $expr[$index] === '{') {
                $close = strpos($expr, '}', $index);
                $quantifier = substr($expr, $index + 1, $close - $index - 1);
                $index = $close + 1;
                if (str_contains($quantifier, ',')) {
                    [$low, $high] = explode(',', $quantifier);
                    $atomMin *= (int) $low;
                    $atomMax *= (int) $high;
                } else {
                    $atomMin *= (int) $quantifier;
                    $atomMax *= (int) $quantifier;
                }
            }

            $min += $atomMin;
            $max += $atomMax;
        }

        return [$min, $max];
    }

    /**
     * Split $expr on top-level occurrences of $delimiter, ignoring any inside a
     * `[...]` character class or `(...)` group.
     *
     * @return list<string>
     */
    private static function splitTopLevel(string $expr, string $delimiter): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $inClass = false;
        $length = strlen($expr);
        for ($index = 0; $index < $length; $index++) {
            $char = $expr[$index];
            if ($char === '\\') {
                $current .= $char.($expr[$index + 1] ?? '');
                $index++;

                continue;
            }
            if ($char === '[') {
                $inClass = true;
            } elseif ($char === ']') {
                $inClass = false;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }

            if ($char === $delimiter && $depth === 0 && ! $inClass) {
                $parts[] = $current;
                $current = '';

                continue;
            }
            $current .= $char;
        }
        $parts[] = $current;

        return $parts;
    }

    /**
     * Index of the `)` that closes the `(` at $open.
     */
    private static function matchingParen(string $expr, int $open): int
    {
        $depth = 0;
        $length = strlen($expr);
        for ($index = $open; $index < $length; $index++) {
            if ($expr[$index] === '\\') {
                $index++;

                continue;
            }
            if ($expr[$index] === '(') {
                $depth++;
            } elseif ($expr[$index] === ')') {
                $depth--;
                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return $length - 1;
    }
}
