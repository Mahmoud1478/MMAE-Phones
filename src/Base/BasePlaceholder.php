<?php

declare(strict_types=1);

namespace MMAE\Phones\Base;

use MMAE\Phones\Configs\PlaceholderData;
use MMAE\Phones\Placeholders\Placeholder;

/**
 * Turns a country's regex schema (`config/phones.php`) into a {@see PlaceholderData}:
 * accepted provider prefixes plus subscriber length. Concrete digit classes are
 * enumerated; open wildcards (`\d`, `[0-9]`) collapse to the mask character.
 *
 * Generic {@see Placeholder} takes an explicit code; per-country subclasses lock theirs.
 */
abstract class BasePlaceholder
{
    /** Used to detect an open (0-9) character class. */
    private const array ALL_DIGITS = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    /**
     * Parsed placeholders shared across instances, keyed by `countryCode|mask`.
     *
     * @var array<string, PlaceholderData>
     */
    private static array $cache = [];

    public function __construct(protected string $countryCode, protected string $mask = 'X') {}

    /**
     * The placeholder for the current country (memoized).
     */
    public function extract(): PlaceholderData
    {
        return self::$cache["{$this->countryCode}|{$this->mask}"] ??= $this->build();
    }

    /**
     * Parse the country schema into a placeholder.
     */
    private function build(): PlaceholderData
    {
        /** @var array<string, string> $country */
        $country = config("phones.{$this->countryCode}", []);

        [$providerPattern, $digitsPattern] = $this->split($country['pattern'] ?? '');
        [$min, $max] = $this->digitLength($digitsPattern);

        return new PlaceholderData(
            code: $country['code'] ?? $this->countryCode,
            key: $country['key'] ?? '',
            localKey: $country['local_key'] ?? '',
            providers: $this->providers($providerPattern),
            digitsMin: $min,
            digitsMax: $max,
            mask: $this->mask,
        );
    }

    /**
     * @return array{string, string} the `provider` and `digits` sub-patterns
     */
    private function split(string $pattern): array
    {
        if (preg_match('/\(\?<provider>(.+?)\)\(\?<digits>(.+?)\)/', $pattern, $m) === 1) {
            return [$m[1], $m[2]];
        }

        return ['', ''];
    }

    /**
     * Expand a provider sub-pattern into every accepted prefix.
     *
     * @return list<string>
     */
    private function providers(string $pattern): array
    {
        $prefixes = [];
        foreach ($this->alternatives($pattern) as $alternative) {
            foreach ($this->expand($alternative) as $prefix) {
                $prefixes[] = $prefix;
            }
        }

        return array_values(array_unique($prefixes));
    }

    /**
     * Split a sub-pattern on top-level `|`, ignoring pipes inside `[...]`.
     *
     * @return list<string>
     */
    private function alternatives(string $pattern): array
    {
        if ($pattern === '') {
            return [];
        }

        $parts = [];
        $buffer = '';
        $inClass = false;
        foreach (str_split($pattern) as $char) {
            if ($char === '[') {
                $inClass = true;
            } elseif ($char === ']') {
                $inClass = false;
            }
            if ($char === '|' && ! $inClass) {
                $parts[] = $buffer;
                $buffer = '';

                continue;
            }
            $buffer .= $char;
        }
        $parts[] = $buffer;

        return $parts;
    }

    /**
     * Expand a single alternative (no top-level `|`) into concrete prefixes.
     *
     * @return list<string>
     */
    private function expand(string $alternative): array
    {
        $prefixes = [''];
        foreach ($this->tokens($alternative) as $options) {
            $next = [];
            foreach ($prefixes as $prefix) {
                foreach ($options as $option) {
                    $next[] = $prefix.$option;
                }
            }
            $prefixes = $next;
        }

        return $prefixes;
    }

    /**
     * Tokenize an alternative into per-token option lists; their cartesian
     * product is the set of accepted prefixes.
     *
     * @return list<list<string>>
     */
    private function tokens(string $alternative): array
    {
        $pattern = '/(?<class>\[[^\]]*\](?:\{\d+(?:,\d+)?\})?)|(?<any>\\\\d(?:\{\d+(?:,\d+)?\})?)|(?<digit>\d)/';
        preg_match_all($pattern, $alternative, $matches, PREG_SET_ORDER);

        $tokens = [];
        foreach ($matches as $match) {
            if (($match['class'] ?? '') !== '') {
                $tokens[] = $this->classOptions($match['class']);
            } elseif (($match['any'] ?? '') !== '') {
                $tokens[] = [str_repeat($this->mask, $this->quantifier($match['any']))];
            } elseif (($match['digit'] ?? '') !== '') {
                $tokens[] = [$match['digit']];
            }
        }

        return $tokens;
    }

    /**
     * Options a character class contributes: its enumerated digits, or one
     * masked string when the class is an open wildcard (all of 0-9) or repeated.
     *
     * @return list<string>
     */
    private function classOptions(string $token): array
    {
        preg_match('/^\[([^\]]*)\](?:\{(\d+)(?:,\d+)?\})?$/', $token, $m);
        $repeat = isset($m[2]) ? (int) $m[2] : 1;
        $digits = $this->classDigits($m[1] ?? '');

        if ($repeat > 1 || $digits === self::ALL_DIGITS) {
            return [str_repeat($this->mask, $repeat)];
        }

        return array_map(static fn (int $digit): string => (string) $digit, $digits);
    }

    /**
     * The sorted, unique digits a class body matches (ranges expanded, stray
     * `|` separators ignored).
     *
     * @return list<int>
     */
    private function classDigits(string $body): array
    {
        $digits = [];
        $chars = str_split(str_replace('|', '', $body));
        $count = count($chars);
        for ($i = 0; $i < $count; $i++) {
            if (! ctype_digit($chars[$i])) {
                continue;
            }
            if (($chars[$i + 1] ?? '') === '-' && ctype_digit($chars[$i + 2] ?? '')) {
                foreach (range((int) $chars[$i], (int) $chars[$i + 2]) as $digit) {
                    $digits[] = $digit;
                }
                $i += 2;

                continue;
            }
            $digits[] = (int) $chars[$i];
        }

        $digits = array_values(array_unique($digits));
        sort($digits);

        return $digits;
    }

    /**
     * The (minimum) repeat count of a `\d`/`\d{n}`/`\d{n,m}` token.
     */
    private function quantifier(string $token): int
    {
        if (preg_match('/\{(\d+)/', $token, $m) === 1) {
            return (int) $m[1];
        }

        return 1;
    }

    /**
     * The min/max subscriber length from a `digits` sub-pattern.
     *
     * @return array{int, int}
     */
    private function digitLength(string $pattern): array
    {
        if (preg_match('/\\\\d\{(\d+)(?:,(\d+))?\}/', $pattern, $m) === 1) {
            $min = (int) $m[1];

            return [$min, isset($m[2]) ? (int) $m[2] : $min];
        }

        if (str_contains($pattern, '\d')) {
            return [1, 1];
        }

        return [0, 0];
    }
}
