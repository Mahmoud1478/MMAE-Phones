<?php

namespace Workbench\App\Support;

/**
 * Builds random phone-number rows for dataset generation.
 *
 * A row is [phone, status, country_code] where:
 *  - valid rows are the international key form {key}{provider}{digits}
 *    (e.g. Saudi Arabia -> 9665XXXXXXXX) for a random country, and
 *  - invalid rows (nonexistent dialing key / wrong length / wrong provider /
 *    garbage) carry an empty country_code.
 *
 * Every valid row is verified against the exact same regex BasePhone builds,
 * so the label is trustworthy.
 */
final class PhoneDataFactory
{
    /** @var list<string> */
    private array $codes;

    /** @var array<string, array{key:string, regex:string, sampler:RegexSampler}> */
    private array $meta = [];

    /** @var array<string, true> */
    private array $existingKeys = [];

    private const LETTERS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @param  array<string, array<string, string>>  $config  the `phones` config
     */
    public function __construct(array $config)
    {
        foreach ($config as $code => $country) {
            $this->existingKeys[$country['key']] = true;
            $regex = $this->buildRegex($country);
            if ($regex === '') {
                continue;
            }
            $this->meta[$code] = [
                'key' => $country['key'],
                'regex' => $regex,
                'sampler' => new RegexSampler($country['pattern']),
            ];
        }
        $this->codes = array_keys($this->meta);
    }

    /**
     * Generate a single row.
     *
     * @return array{0:string, 1:string, 2:string} [phone, status, country_code]
     */
    public function row(int $validPercent): array
    {
        if (mt_rand(1, 100) <= $validPercent) {
            return $this->validRow();
        }

        return $this->invalidRow();
    }

    /**
     * @return array{0:string, 1:string, 2:string}
     */
    private function validRow(): array
    {
        do {
            $code = $this->codes[mt_rand(0, count($this->codes) - 1)];
            $m = $this->meta[$code];
            $phone = $m['key'].$m['sampler']->sample();
        } while (preg_match($m['regex'], $phone) !== 1);

        return [$phone, 'valid', $code];
    }

    /**
     * @return array{0:string, 1:string, 2:string}
     */
    private function invalidRow(): array
    {
        $strategy = mt_rand(1, 100);

        if ($strategy <= 40) {
            // nonexistent country key
            $code = $this->codes[mt_rand(0, count($this->codes) - 1)];
            $phone = $this->fakeKey().$this->meta[$code]['sampler']->sample();

            return [$phone, 'invalid', ''];
        }

        if ($strategy <= 60) {
            // valid key, wrong digit length
            $code = $this->codes[mt_rand(0, count($this->codes) - 1)];
            $m = $this->meta[$code];
            $phone = $m['key'].$m['sampler']->sample().$this->randDigits(mt_rand(1, 3));
            if (preg_match($m['regex'], $phone) === 1) {
                return $this->invalidRow();
            }

            return [$phone, 'invalid', ''];
        }

        if ($strategy <= 80) {
            // valid key, wrong provider prefix
            $code = $this->codes[mt_rand(0, count($this->codes) - 1)];
            $m = $this->meta[$code];
            $phone = $m['key'].'0'.$this->randDigits(mt_rand(6, 9));
            if (preg_match($m['regex'], $phone) === 1) {
                return $this->invalidRow();
            }

            return [$phone, 'invalid', ''];
        }

        // garbage / letters
        $len = mt_rand(3, 12);
        $s = '';
        for ($i = 0; $i < $len; $i++) {
            $s .= mt_rand(0, 2) === 0
                ? self::LETTERS[mt_rand(0, 25)]
                : (string) mt_rand(0, 9);
        }

        return [$s, 'invalid', ''];
    }

    /**
     * Build the exact validation regex BasePhone::regex() produces.
     *
     * @param  array<string, string>  $country
     */
    private function buildRegex(array $country): string
    {
        $key = $country['key'] ?? '';
        $pattern = $country['pattern'] ?? '';
        if ($key === '' || $pattern === '') {
            return '';
        }
        $local = $country['local_key'] ?? '';
        $alternatives = "(\\+|00)?{$key}";
        if ($local !== '') {
            $alternatives .= "|{$local}";
        }

        return "/^(?<key>{$alternatives})?".$pattern.'$/';
    }

    /**
     * A numeric dialing key that exists in no country.
     */
    private function fakeKey(): string
    {
        do {
            $k = (string) mt_rand(1, 9).$this->randDigits(mt_rand(1, 3));
        } while (isset($this->existingKeys[$k]));

        return $k;
    }

    private function randDigits(int $n): string
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= mt_rand(0, 9);
        }

        return $s;
    }
}
