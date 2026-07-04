<?php

namespace MMAE\Phones\Base;

use Illuminate\Contracts\Support\Arrayable;
use MMAE\Phones\Phone;
use MMAE\Phones\Phones\EGPhone;

/**
 * Base class for every country phone number.
 *
 * Wraps a raw input string and, using the per-country schema in
 * `config/phones.php` (`key`, `local_key`, `pattern`), validates it and
 * normalizes it to a single canonical form. Concrete country classes
 * ({@see EGPhone}, ...) lock a country code in their
 * constructor; the generic {@see Phone} takes an explicit code.
 *
 * Accepts any prefix shape — local trunk `0`, `00`, `+`, or the bare dialing
 * code — and exposes the number as a canonical string ({@see toString()}),
 * every accepted variant ({@see all()}), or its parts ({@see segments()}).
 *
 * Implements Stringable (casts to the canonical form, `''` when invalid) and
 * Arrayable ({@see toArray()} returns {@see all()}). The `+` prefix of the
 * string output is controlled by the static {@see $plus} flag via
 * {@see withPlus()} / {@see withoutPlus()}.
 *
 * @implements Arrayable<int, string>
 */
abstract class BasePhone implements \Stringable, Arrayable
{
    /**
     * @var array<string, string>
     */
    private array $country;

    /**
     *  add (+) or not by default
     */
    public static bool $plus = false;

    private string $normalizedNumber;

    public function __construct(private readonly string $number, string $countryCode)
    {
        $this->for($countryCode)->normalize();
    }

    /**
     * add (+) when cast to string
     */
    public function withPlus(): self
    {
        static::$plus = true;

        return $this;
    }

    /**
     * remove (+) when cast to string
     *
     * @return $this
     */
    public function withoutPlus(): self
    {
        static::$plus = false;

        return $this;
    }

    /**
     * build the full validation regex from the country schema
     *
     * key group is derived from the country calling code (`key`) and the
     * optional national trunk prefix (`local_key`); the body comes from `pattern`.
     */
    private function regex(): string
    {
        $key = $this->country['key'] ?? '';
        $pattern = $this->country['pattern'] ?? '';
        if ($key === '' || $pattern === '') {
            return '';
        }
        $local = $this->country['local_key'] ?? '';
        $alternatives = "(\+|00)?{$key}";
        if ($local !== '') {
            $alternatives .= "|{$local}";
        }

        return "/^(?<key>{$alternatives})?".$pattern.'$/';
    }

    /**
     * every accepted prefix form, derived from `key` and `local_key`
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
     * determine if the phone number is valid
     */
    public function isValid(): bool
    {
        $regex = $this->regex();
        if ($regex === '') {
            return false;
        }

        return (bool) preg_match($regex, $this->normalizedNumber);
    }

    /**
     * determine if the phone number is not valid
     */
    public function isNotValid(): bool
    {
        return ! $this->isValid();
    }

    /**
     * set the country code
     */
    public function for(string $countryCode): BasePhone
    {
        /** @var array<string, string> $country */
        $country = config("phones.$countryCode", []);
        $this->country = $country;

        return $this;
    }

    /**
     * get full string version of the phone number with country key
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
     * get all possible shape of the phone number
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
     * get phone number parts
     *
     * @return array<int|string, string>
     */
    public function segments(): array
    {
        preg_match($this->regex(), $this->normalizedNumber, $matches);

        return $matches;
    }

    /**
     * get key from phone schema
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
     * get the given number
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
