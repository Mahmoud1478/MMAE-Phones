<?php

namespace MMAE\Phones\Base;

use Illuminate\Contracts\Support\Arrayable;

abstract class BasePhone implements \Stringable, Arrayable
{
    /**
     * @var array $country
     */
    private array $country;
    /**
     *  add (+) or not by default
     * @var bool $plus
     */
    public static bool $plus = false;

    /**
     * create new object
     * @param string $number
     * @param string $countryCode
     */

    private string $normalizedNumber;

    public function __construct(private readonly string $number, string $countryCode)
    {
        $this->for($countryCode)->normalize();
    }

    /**
     * add (+) when cast to string
     *
     * @return BasePhone
     */
    public function withPlus(): self
    {
        static::$plus = true;
        return $this;
    }

    /**
     * remove (+) when cast to string
     * @return $this
     */
    public function withoutPlus(): self
    {
        static::$plus = false;
        return $this;
    }

    /**
     * determine if the phone number is valid
     * @return bool
     */
    public function isValid(): bool
    {
        $regex = $this->config('regex');
        if (!$regex) {
            return false;
        }
        return preg_match($regex, $this->normalizedNumber);
    }

    /**
     * determine if the phone number is not valid
     * @return bool
     */
    public function isNotValid(): bool
    {
        return !$this->isValid();
    }

    /**
     * set the country code
     * @param string $countryCode
     * @return $this
     */
    public function for(string $countryCode): BasePhone
    {
        $this->country = config("phones.$countryCode", []);
        return $this;
    }

    /**
     * get full string version of the phone number with country key
     * @return string
     */
    public function toString(): string
    {
        if ($this->isNotValid()) {
            return '';
        }
        $segments = $this->segments();
        $key = static::$plus ? "+" : '';
        $key .= $this->country['key'];
        return "{$key}{$segments['provider']}{$segments['digits']}";
    }

    /**
     * get all possible shape of the phone number
     * @return array
     */
    public function all(): array
    {
        if ($this->isNotValid()) {
            return [];
        }
        $segments = $this->segments();
        $base = $segments['provider'] . $segments['digits'];
        return array_map(function ($key) use ($base) {
            return $key . $base;
        }, $this->config('all_keys'));
    }

    /**
     * get phone number parts
     * @return array
     */
    public function segments(): array
    {
        preg_match($this->config('regex'), $this->normalizedNumber, $matches);
        return $matches;
    }

    /**
     * get key from phone schema
     * @param string|null $key
     * @param $default
     * @return array|mixed|null
     */

    public function config(string $key = null, $default = null): mixed
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
     * @return string
     */
    public function number(): string
    {
        return $this->number;
    }

    public function toArray(): array
    {
        return $this->all();
    }

    private function normalize(): string
    {
        $this->normalizedNumber = str_replace([' ', '-'], ['', ''], $this->number);
        return $this;

    }

}
