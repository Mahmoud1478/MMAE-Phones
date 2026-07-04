<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use Illuminate\Contracts\Support\Arrayable;
use MMAE\Phones\Base\BasePlaceholder;

/**
 * Placeholder description for a single country, built by {@see BasePlaceholder::extract()}.
 *
 * `providers` holds every accepted dialing prefix (enumerated, or masked with
 * `mask` where the schema accepts any digit); `digitsMin`/`digitsMax` are the
 * subscriber part length. Use `bareFormat()`/`localFormat()`/`internationalFormat()`
 * for masked templates, or `examples()`/`toArray()` for concrete samples.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class PlaceholderData implements Arrayable
{
    /** Masked subscriber part, e.g. 'XXXXXXXX'; derived once. */
    private string $digitsMaskCache;

    /** Every provider collapsed into one compact token; derived once. */
    private string $providerMaskCache;

    /**
     * @param  list<string>  $providers  accepted provider prefixes, e.g. ['10', '11', '12', '15']
     */
    public function __construct(
        public string $code,
        public string $key,
        public string $localKey,
        public array $providers,
        public int $digitsMin,
        public int $digitsMax,
        public string $mask = 'X',
    ) {
        $this->digitsMaskCache = str_repeat($mask, $digitsMax);
        $this->providerMaskCache = $this->collapse($providers);
    }

    /**
     * The first (canonical) provider prefix, or '' when there is none.
     */
    public function provider(): string
    {
        return $this->providers[0] ?? '';
    }

    /**
     * The masked subscriber part, e.g. 'XXXXXXXX' for 8 digits.
     */
    public function digitsMask(): string
    {
        return $this->digitsMaskCache;
    }

    /**
     * Bare national number for one provider, e.g. '10XXXXXXXX'.
     */
    public function bare(?string $provider = null): string
    {
        return ($provider ?? $this->provider()).$this->digitsMask();
    }

    /**
     * Local (trunk-prefixed) form, e.g. '010XXXXXXXX'.
     * Falls back to the bare form when the country has no local key.
     */
    public function local(?string $provider = null): string
    {
        return $this->localKey.$this->bare($provider);
    }

    /**
     * International form, e.g. '+2010XXXXXXXX' (or '2010XXXXXXXX' without plus).
     */
    public function international(bool $plus = true, ?string $provider = null): string
    {
        return ($plus ? '+' : '').$this->key.$this->bare($provider);
    }

    /**
     * All provider prefixes collapsed into one compact token, e.g.
     * '1[0,1,2,5]' for ['10','11','12','15'] or '[3,5,6,7]X' for masked sets.
     */
    public function providerMask(): string
    {
        return $this->providerMaskCache;
    }

    /**
     * Bare national format covering every provider, e.g. '1[0,1,2,5]XXXXXXXX'.
     */
    public function bareFormat(): string
    {
        return $this->providerMask().$this->digitsMask();
    }

    /**
     * Local (trunk-prefixed) format covering every provider, e.g. '01[0,1,2,5]XXXXXXXX'.
     */
    public function localFormat(): string
    {
        return $this->localKey.$this->bareFormat();
    }

    /**
     * International format covering every provider, e.g. '+201[0,1,2,5]XXXXXXXX'.
     */
    public function internationalFormat(bool $plus = true): string
    {
        return ($plus ? '+' : '').$this->key.$this->bareFormat();
    }

    /**
     * Collapse equal-length prefixes column by column (shared digits stay
     * literal, varying digits become `[a,b,c]`); mixed lengths list wholesale.
     *
     * @param  list<string>  $prefixes
     */
    private function collapse(array $prefixes): string
    {
        if ($prefixes === []) {
            return '';
        }
        if (count($prefixes) === 1) {
            return $prefixes[0];
        }

        $lengths = array_unique(array_map(strlen(...), $prefixes));
        if (count($lengths) !== 1) {
            return '['.implode(',', $prefixes).']';
        }

        $token = '';
        for ($column = 0; $column < $lengths[array_key_first($lengths)]; $column++) {
            $chars = array_values(array_unique(array_map(static fn (string $prefix): string => $prefix[$column], $prefixes)));
            $token .= count($chars) === 1 ? $chars[0] : '['.implode(',', $chars).']';
        }

        return $token;
    }

    /**
     * Every shape for every provider prefix.
     *
     * @return list<array{provider: string, bare: string, local: string, international: string}>
     */
    public function examples(): array
    {
        return array_map(fn (string $provider): array => [
            'provider' => $provider,
            'bare' => $this->bare($provider),
            'local' => $this->local($provider),
            'international' => $this->international(true, $provider),
        ], $this->providers);
    }

    /**
     * @return array{
     *     code: string,
     *     key: string,
     *     local_key: string,
     *     providers: list<string>,
     *     digits: array{min: int, max: int, mask: string},
     *     format: array{bare: string, local: string, international: string},
     *     example: array{bare: string, local: string, international: string},
     *     examples: list<array{provider: string, bare: string, local: string, international: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'key' => $this->key,
            'local_key' => $this->localKey,
            'providers' => $this->providers,
            'digits' => [
                'min' => $this->digitsMin,
                'max' => $this->digitsMax,
                'mask' => $this->digitsMask(),
            ],
            'format' => [
                'bare' => $this->bareFormat(),
                'local' => $this->localFormat(),
                'international' => $this->internationalFormat(),
            ],
            'example' => [
                'bare' => $this->bare(),
                'local' => $this->local(),
                'international' => $this->international(),
            ],
            'examples' => $this->examples(),
        ];
    }
}
