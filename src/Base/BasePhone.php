<?php

namespace MMAE\Phones\Base;

use Illuminate\Contracts\Support\Arrayable;
use MMAE\Phones\Phone;
use MMAE\Phones\Phones\EGPhone;

/**
 * Base for every country phone number: wraps a raw string, validates and
 * normalizes it against the per-country schema in `config/phones.php`
 * (`key`, `local_key`, `pattern`). Accepts any prefix shape (`0`, `00`, `+`,
 * or the bare dialing code).
 *
 * Concrete classes ({@see EGPhone}, ...) hardcode a code; {@see Phone} takes one.
 *
 * Casts to the canonical string ({@see toString()}, `''` when invalid); also
 * exposes every accepted variant ({@see all()}) and the parsed parts
 * ({@see segments()}). The `+` prefix is toggled by the static {@see $plus}
 * flag via {@see withPlus()} / {@see withoutPlus()}.
 *
 * @implements Arrayable<int, string>
 */
abstract class BasePhone implements \Stringable, Arrayable
{
    /**
     * @var array<string, string>
     */
    private array $country;

    /** Whether string output carries a leading `+`. Toggle via withPlus()/withoutPlus(). */
    public static bool $plus = false;

    private string $normalizedNumber;

    /** Memoized regex built from the current country schema (null until first built). */
    private ?string $regexCache = null;

    /**
     * Memoized match against the normalized number (null until first run).
     *
     * @var array<int|string, string>|null
     */
    private ?array $matchesCache = null;

    public function __construct(private readonly string $number, string $countryCode)
    {
        $this->for($countryCode)->normalize();
    }

    /**
     * Include a leading `+` when cast to string.
     */
    public function withPlus(): self
    {
        static::$plus = true;

        return $this;
    }

    /**
     * Drop the leading `+` when cast to string.
     *
     * @return $this
     */
    public function withoutPlus(): self
    {
        static::$plus = false;

        return $this;
    }

    /**
     * Build the validation regex from the country schema. The key group comes
     * from the calling code (`key`) plus optional trunk prefix (`local_key`);
     * the body is `pattern`. Returns `''` when the schema is missing.
     */
    private function regex(): string
    {
        if ($this->regexCache !== null) {
            return $this->regexCache;
        }

        $key = $this->country['key'] ?? '';
        $pattern = $this->country['pattern'] ?? '';
        if ($key === '' || $pattern === '') {
            return $this->regexCache = '';
        }
        $local = $this->country['local_key'] ?? '';
        $alternatives = "(\+|00)?{$key}";
        if ($local !== '') {
            $alternatives .= "|{$local}";
        }

        return $this->regexCache = "/^(?<key>{$alternatives})?".$pattern.'$/';
    }

    /**
     * Every accepted prefix form (`+key`, `00key`, `key`, and `local_key`).
     *
     * @return list<string>
     */
    private function keys(): array
    {
        $key = $this->country['key'] ?? '';
        $local = $this->country['local_key'] ?? '';
        $keys = ["+{$key}", "00{$key}", $key];
        if ($local !== '') {
            $keys[] = $local;
        }

        return array_values(array_unique($keys));
    }

    /**
     * Whether the number matches its country pattern.
     */
    public function isValid(): bool
    {
        return $this->segments() !== [];
    }

    /**
     * Inverse of {@see isValid()}.
     */
    public function isNotValid(): bool
    {
        return ! $this->isValid();
    }

    /**
     * Load the schema for a country code (silently empty if unknown).
     */
    public function for(string $countryCode): BasePhone
    {
        /** @var array<string, string> $country */
        $country = config("phones.$countryCode", []);
        $this->country = $country;
        $this->regexCache = null;
        $this->matchesCache = null;

        return $this;
    }

    /**
     * Canonical form (`key + provider + digits`), or `''` when invalid.
     */
    public function toString(): string
    {
        if ($this->isNotValid()) {
            return '';
        }
        $segments = $this->segments();
        $key = static::$plus ? '+' : '';
        $key .= $this->country['key'] ?? '';

        return "{$key}{$segments['provider']}{$segments['digits']}";
    }

    /**
     * The number under every accepted prefix form, or `[]` when invalid.
     *
     * @return list<string>
     */
    public function all(): array
    {
        if ($this->isNotValid()) {
            return [];
        }
        $segments = $this->segments();
        $base = $segments['provider'].$segments['digits'];

        return array_map(fn (string $key): string => $key.$base, $this->keys());
    }

    /**
     * Regex match groups (`key`, `provider`, `digits`); empty when no match.
     *
     * @return array<int|string, string>
     */
    public function segments(): array
    {
        if ($this->matchesCache !== null) {
            return $this->matchesCache;
        }

        $regex = $this->regex();
        if ($regex === '') {
            return $this->matchesCache = [];
        }
        preg_match($regex, $this->normalizedNumber, $matches);

        return $this->matchesCache = $matches;
    }

    /**
     * Read the country schema: one key, or the whole array when `$key` is null.
     *
     * @return ($key is null ? array<string, string> : string|mixed)
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->country;
        }

        return $this->country[$key] ?? $default;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * The raw number as originally passed in.
     */
    public function number(): string
    {
        return $this->number;
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    private function normalize(): void
    {
        $this->normalizedNumber = str_replace([' ', '-'], '', $this->number);
    }
}
