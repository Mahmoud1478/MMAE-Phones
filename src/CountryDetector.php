<?php

declare(strict_types=1);

namespace MMAE\Phones;

use MMAE\Phones\Commands\BuildLookupCommand;

/**
 * Resolve which configured countries an international phone number could belong to.
 *
 * Detection is **international-only**: the number must carry a country dialing
 * code (`+CC`, `00CC`, or a bare `CC` prefix). A national/local number (trunk
 * `0` prefix, no dialing code) carries no country information and yields `[]` —
 * for those the caller already knows the country and should use {@see Phone}.
 *
 * Dialing codes are shared across countries (every NANP territory is `+1`), so
 * detection returns *every* matching code in config order rather than guessing a
 * single winner; the caller decides.
 *
 * Built for bulk use. Rather than scan all ~200 countries on every call, matching
 * first jumps to the candidates whose numbers are exactly the input's length,
 * then walks a dialing-code trie one digit at a time — so a lookup only tests the
 * handful of same-length countries that share the number's leading code, and an
 * impossible length is rejected up front. That length-first index is loaded
 * verbatim from the precompiled `config/phone-lookup.php` when present (generated
 * by `php artisan phones:build-lookup`) — a plain assignment, no runtime build;
 * otherwise it is compiled from `config/phones.php` at runtime via
 * {@see compileByLength()}.
 *
 * The index is loaded once and cached in memory for the rest of the process — the
 * hot path is a single bool check, with no `config()` resolve or array comparison
 * per call. If you mutate `config('phones')` / `config('phone-lookup')` at runtime
 * (e.g. in tests), call {@see flush()} to force a reload.
 *
 * ```php
 * CountryDetector::detect('+201000000000'); // ['EG']
 * CountryDetector::detect('+15551234567');  // ['US', 'CA', ...] every matching NANP code
 * CountryDetector::detect('01000000000');   // [] — local form, no dialing code
 * ```
 */
final class CountryDetector
{
    /**
     * whether {@see $trie} has been loaded for this process; the hot-path guard,
     * so `detect()` never touches `config()` after the first call
     */
    private static bool $compiled = false;

    /**
     * length-first index: total (dialing code + subscriber) length => a dialing-
     * code trie of only the countries whose numbers are exactly that long. Within
     * a length bucket, nested single-digit nodes spell each dialing code; a leaf's
     * `$` key holds the [regex, countries] buckets. {@see detect()} jumps straight
     * to the input's length bucket (impossible length => instant miss), then walks
     * the dialing code — no per-leaf length gate, since the length is already
     * fixed. Loaded verbatim from the baked lookup file (no runtime build) or
     * compiled via {@see compileByLength()} when the file is absent.
     *
     * @var array<int, mixed>
     */
    private static array $byLength = [];

    /**
     * every configured country code whose international format matches $number,
     * in config order
     *
     * @return list<string>
     */
    public static function detect(string $number): array
    {
        if (! self::$compiled) {
            self::compile();
        }

        // Normalize: only rebuild the string when a space or dash is actually
        // present (strpbrk is a single scan, no allocation) — the clean E.164 fast
        // path skips the str_replace copy. Then drop a leading + or 00 prefix.
        $national = strpbrk($number, ' -') === false ? $number : str_replace([' ', '-'], '', $number);
        if (str_starts_with($national, '+')) {
            $national = substr($national, 1);
        } elseif (str_starts_with($national, '00')) {
            $national = substr($national, 2);
        }
        $length = strlen($national);

        // Jump straight to the dialing codes whose numbers are exactly this long.
        // A miss means no country has a number of this length — impossible input,
        // rejected before any walk (this also subsumes the old per-leaf gate).
        $node = self::$byLength[$length] ?? null;
        if (! is_array($node)) {
            return [];
        }

        // Walk the dialing code within that length's candidates, one digit at a
        // time, stopping as soon as no branch continues. A node's `$` entry marks
        // a full dialing code: test its patterns against the remaining subscriber
        // digits. The length is already fixed by the bucket, so no length gate is
        // needed. Keyed by config ordinal so results keep config order and dedupe.
        $matches = [];
        for ($position = 0; $position < $length; $position++) {
            // single hash lookup per digit; is_array() both detects an absent
            // branch and narrows the mixed trie value into the array to walk on
            $branch = $node[$national[$position]] ?? null;
            if (! is_array($branch)) {
                break;
            }
            $node = $branch;

            // dialing code = first ($next) digits; need >= 1 subscriber digit
            $leaf = $node['$'] ?? null;
            if (is_array($leaf)) {
                $next = $position + 1;
                if ($next >= $length) {
                    continue;
                }

                $subscriber = substr($national, $next);
                foreach ($leaf as $bucket) {
                    if (! is_array($bucket)) {
                        continue;
                    }
                    [$pattern, $countries] = $bucket;
                    if (is_string($pattern) && is_array($countries) && preg_match($pattern, $subscriber)) {
                        foreach ($countries as $entry) {
                            if (! is_array($entry)) {
                                continue;
                            }
                            [$ordinal, $code] = $entry;
                            if (is_int($ordinal) && is_string($code)) {
                                $matches[$ordinal] = $code;
                            }
                        }
                    }
                }
            }
        }

        // common case is 0 or 1 match — only sort when several codes collided
        if (count($matches) > 1) {
            ksort($matches);
        }

        return array_values($matches);
    }

    /**
     * the first configured country code that matches, or null when none do
     */
    public static function detectFirst(string $number): ?string
    {
        return self::detect($number)[0] ?? null;
    }

    /**
     * Drop the cached index so the next {@see detect()} reloads from the current
     * config. Call after mutating `config('phones')` / `config('phone-lookup')`
     * at runtime.
     */
    public static function flush(): void
    {
        self::$compiled = false;
        self::$byLength = [];
    }

    /**
     * Load the length-first index once and cache it for the process.
     *
     * Prefers the precompiled `config/phone-lookup.php` (see
     * {@see BuildLookupCommand}), which already stores the ready-to-walk index —
     * so this is a plain assignment, no runtime transform. When that file is
     * absent it falls back to compiling from `config/phones.php` at runtime, so
     * detection still works if an app extends the schema without regenerating.
     * Guarded by {@see $compiled} and reset only via {@see flush()}.
     */
    private static function compile(): void
    {
        self::$compiled = true;

        /** @var array<string, mixed>|null $lookup */
        $lookup = config('phone-lookup');
        if (is_array($lookup) && isset($lookup['byLength']) && is_array($lookup['byLength'])) {
            /** @var array<int, mixed> $byLength */
            $byLength = $lookup['byLength'];
            self::$byLength = $byLength;

            return;
        }

        /** @var array<string, array<string, string>> $countries */
        $countries = config('phones', []);
        self::$byLength = self::compileByLength($countries);
    }

    /**
     * Compile a `phones` schema straight into the length-first index — the single
     * entry point shared by the runtime fallback and {@see BuildLookupCommand}, so
     * the baked file and the fallback always produce the same structure.
     *
     * @param  array<string, array<string, string>>  $countries
     * @return array<int, mixed>
     */
    public static function compileByLength(array $countries): array
    {
        return self::buildByLength(self::compileIndex($countries)['index']);
    }

    /**
     * Distribute the flat dialing-code index into a length-first index: the top
     * key is a total (dialing code + subscriber) length, and each value is a
     * dialing-code trie of only the countries whose numbers are exactly that long.
     * A variable-length pattern (e.g. `\d{6,7}`) lands in every total it can
     * produce. Because the length is fixed by the top key, leaves carry only the
     * `$` => [regex, countries] buckets — no length gate — and {@see detect()}
     * rejects an impossible length with a single top-level miss.
     *
     * @param  array<int|string, list<array{0: string, 1: list<array{0: int, 1: string}>, 2: int, 3: int}>>  $index
     * @return array<int, mixed>
     */
    private static function buildByLength(array $index): array
    {
        $byLength = [];
        foreach ($index as $key => $buckets) {
            $key = (string) $key;
            $keyLength = strlen($key);
            $digits = $keyLength === 0 ? [] : str_split($key);
            foreach ($buckets as [$pattern, $countries, $bucketMin, $bucketMax]) {
                $leaf = [$pattern, $countries];
                for ($subscriber = $bucketMin; $subscriber <= $bucketMax; $subscriber++) {
                    $total = $keyLength + $subscriber;
                    $byLength[$total] = self::attach($byLength[$total] ?? [], $digits, $leaf);
                }
            }
        }

        return $byLength;
    }

    /**
     * Insert one leaf under the dialing-code path $digits, creating trie nodes as
     * needed, and return the updated node. Pure (no references) so the nested
     * structure stays statically typed. Compile-time only, never on the hot path.
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
     * Compile a `phones` schema into the lookup shape consumed by {@see detect()}
     * and baked into `config/phone-lookup.php`.
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
     * The [min, max] subscriber length an anchored detection regex accepts, so
     * {@see detect()} can reject a wrong-length number before paying for `substr`
     * and `preg_match`. Handles the schema's pattern vocabulary — named or
     * non-capturing groups, character classes, `\d`-style escapes, literals,
     * top-level alternation, and `{n}` / `{m,n}` quantifiers (no `+`/`*`/`?` or
     * nested groups occur). Runs at compile time only, never on the hot path.
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
