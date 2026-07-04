<?php

declare(strict_types=1);

namespace MMAE\Phones;

use MMAE\Phones\Support\LookupCompiler;

/**
 * Detects which configured countries an international phone number could belong to.
 *
 * Pass a number in international form — `+CC…`, `00CC…`, or a bare `CC…`, with any
 * spaces or dashes — and get back the matching ISO country codes:
 *
 * ```php
 * CountryDetector::detect('+201000000000');      // ['EG']
 * CountryDetector::detect('+1 555-1234567');     // ['US', 'CA', ...] — every +1 (NANP) match
 * CountryDetector::detectFirst('+201000000000'); // 'EG'  (or null if none)
 * CountryDetector::detect('01000000000');        // []    — local form, no dialing code
 * ```
 *
 * A shared dialing code matches several countries (every NANP country is `+1`), so
 * `detect()` returns all of them in config order and lets you decide;
 * `detectFirst()` takes the first. A local/national number (leading trunk `0`, no
 * dialing code) has no country to detect and returns `[]` — you already know its
 * country there, so use {@see Phone} instead.
 *
 * Detection reads a precompiled index (`config/phone-lookup.php`) that the package
 * ships and loads once per process. It is **required** — regenerate it with
 * `php artisan phones:build-lookup` after editing `config/phones.php`. If you change
 * the config at runtime (e.g. in a test), call {@see flush()} to reload.
 */
final class CountryDetector
{
    /** True once the index has been loaded this process (loaded lazily on first detect). */
    private static bool $compiled = false;

    /**
     * The loaded detection index, keyed by total number length. Built by
     * {@see LookupCompiler} and baked into `config/phone-lookup.php`; empty until
     * the first {@see detect()} loads it. See {@see LookupCompiler} for the shape.
     *
     * @var array<int, mixed>
     */
    private static array $index = [];

    /**
     * Returns the ISO codes of every configured country whose international format
     * matches $number, in config order — or `[]` if none match or the number is
     * not in international form.
     *
     * @return list<string>
     */
    public static function detect(string $number): array
    {
        return self::scan($number, false);
    }

    /**
     * Like {@see detect()}, but returns only the first matching code, or null.
     *
     * Runs the same walk as {@see detect()} but bails the instant the first country
     * matches — it never collects the rest — so it does no more work than needed
     * when you only want one answer.
     */
    public static function detectFirst(string $number): ?string
    {
        return self::scan($number, true)[0] ?? null;
    }

    /**
     * Shared walk behind {@see detect()} and {@see detectFirst()}.
     *
     * When $stopAtFirst is false, collects every matching country (config order).
     * When true, returns a single-element list `[$code]` as soon as one country
     * matches and skips the remaining walk — the early-exit path for
     * {@see detectFirst()}.
     *
     * @return list<string>
     */
    private static function scan(string $number, bool $stopAtFirst): array
    {
        if (! self::$compiled) {
            self::compile();
        }

        // Normalize to bare national digits: strip spaces/dashes, then a leading
        // + or 00 dialing prefix.
        $national = strpbrk($number, ' -') === false ? $number : str_replace([' ', '-'], '', $number);
        if (str_starts_with($national, '+')) {
            $national = substr($national, 1);
        } elseif (str_starts_with($national, '00')) {
            $national = substr($national, 2);
        }
        $length = strlen($national);

        // Jump to the bucket of countries whose numbers are exactly this long; a
        // miss means no country uses this length, so the input is impossible.
        $node = self::$index[$length] ?? null;
        if ($node === null) {
            return [];
        }

        // Walk the dialing code one digit at a time, descending the trie until no
        // branch continues. A node's `$`/`#` entries mark a full dialing code:
        // test the remaining subscriber digits there. Matches are keyed by config
        // ordinal so results keep config order and dedupe.
        $matches = [];
        for ($position = 0; $position < $length; $position++) {
            $branch = $node[$national[$position]] ?? null;
            if ($branch === null) {
                break;
            }
            $node = $branch;

            // `#` = fixed-width-provider map (a shared dialing code's literal
            // prefixes, e.g. every +1 territory), `$` = regex buckets. Both need
            // >= 1 subscriber digit past the dialing code.
            $exact = $node['#'] ?? null;
            $leaf = $node['$'] ?? null;
            if ($exact === null && $leaf === null) {
                continue;
            }

            $next = $position + 1;
            if ($next >= $length) {
                continue;
            }
            $subscriber = substr($national, $next);

            // Exact path: match the leading provider digits via hash lookup.
            // `ctype_digit` guards the subscriber since the map assumes all-digits.
            if ($exact !== null && ctype_digit($subscriber)) {
                foreach ($exact as $width => $map) {
                    $hit = $map[substr($subscriber, 0, $width)] ?? null;
                    if ($hit === null) {
                        continue;
                    }
                    foreach ($hit as [$ordinal, $code]) {
                        if ($stopAtFirst) {
                            return [$code];
                        }
                        $matches[$ordinal] = $code;
                    }
                }
            }

            if ($leaf !== null) {
                foreach ($leaf as [$pattern, $countries]) {
                    if (preg_match($pattern, $subscriber)) {
                        foreach ($countries as [$ordinal, $code]) {
                            if ($stopAtFirst) {
                                return [$code];
                            }
                            $matches[$ordinal] = $code;
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
     * Forget the loaded index so the next {@see detect()} reloads it. Call after
     * changing `config('phone-lookup')` at runtime, e.g. in tests.
     */
    public static function flush(): void
    {
        self::$compiled = false;
        self::$index = [];
    }

    /**
     * Load and cache the precompiled index for this process (once; reset via
     * {@see flush()}). The index is required — there is no runtime fallback.
     *
     * @throws \RuntimeException if the index is missing; regenerate it with
     *                           `php artisan phones:build-lookup`
     */
    private static function compile(): void
    {
        /** @var array<string, mixed>|null $lookup */
        $lookup = config('phone-lookup');
        if (! is_array($lookup) || ! isset($lookup['index']) || ! is_array($lookup['index'])) {
            throw new \RuntimeException(
                'mmae/phones: no compiled detection index found at config("phone-lookup.index"). '.
                'Generate it with `php artisan phones:build-lookup` (re-run it after editing config/phones.php).'
            );
        }

        /** @var array<int, mixed> $index */
        $index = $lookup['index'];
        self::$index = $index;
        self::$compiled = true;
    }
}
