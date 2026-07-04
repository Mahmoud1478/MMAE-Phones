<?php

declare(strict_types=1);

namespace MMAE\Phones\Base;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use MMAE\Phones\Configs\AllowEmptyConfig;
use MMAE\Phones\Configs\ExistsConfig;
use MMAE\Phones\Configs\FormatConfig;
use MMAE\Phones\Configs\NullableConfig;
use MMAE\Phones\Configs\RuleConfig;
use MMAE\Phones\Configs\UniqueConfig;
use MMAE\Phones\Defaults\Rules;
use MMAE\Phones\Phone;
use MMAE\Phones\Rules\EGPhoneRule;
use MMAE\Phones\Rules\PhoneRule;

/**
 * Fluent, chainable Laravel ValidationRule for a country phone number.
 *
 * Build with {@see make()}, then chain {@see message()}, {@see nullable()},
 * {@see required()}, {@see exists()}, {@see unique()}, or {@see validateUsing()}.
 * The rule is implicit, so null/empty/absent values reach it and are handled by
 * its own nullable()/allowEmpty()/required() logic. The default flow checks
 * format first (the DB is never queried for an invalid number), then optional
 * exists/unique checks against every accepted shape ({@see BasePhone::all()});
 * {@see validateUsing()} replaces it entirely.
 *
 * Concrete country rules ({@see EGPhoneRule}, ...) lock their code; the generic
 * {@see PhoneRule} takes an explicit code.
 */
abstract class BasePhoneRule implements ValidationRule
{
    /**
     * Implicit flag: keeps Laravel running the rule on null/empty/absent values
     * so nullable()/allowEmpty()/required() can handle them.
     */
    public bool $implicit = true;

    /**
     * the country code the rule validates against
     */
    protected string $countryCode = '';

    /**
     * pass and skip every check when the value is null
     */
    protected bool $nullable = false;

    /**
     * pass and skip every check when the value is an empty string
     */
    protected bool $allowEmpty = false;

    /**
     * Full-flow override set by {@see validateUsing()}; replaces the entire
     * format/exists/unique flow when present.
     *
     * @var (Closure(BasePhone, string, mixed, RuleConfig, Closure(string): mixed): void)|null
     */
    protected ?Closure $callback = null;

    /**
     * format check config
     *
     * @var array{message: string}
     */
    protected array $formatRule = [
        'message' => 'phones::validation.phone',
    ];

    /**
     * existence check config; disabled by default so it always passes
     *
     * @var array{enabled: bool, table: string, column: ?string, message: string}
     */
    protected array $existsRule = [
        'enabled' => false,
        'table' => '',
        'column' => null,
        'message' => 'phones::validation.exists',
    ];

    /**
     * uniqueness check config; disabled by default so it always passes
     *
     * @var array{enabled: bool, table: string, column: ?string, ignore: mixed, ignoreColumn: string, message: string}
     */
    protected array $uniqueRule = [
        'enabled' => false,
        'table' => '',
        'column' => null,
        'ignore' => null,
        'ignoreColumn' => 'id',
        'message' => 'phones::validation.unique',
    ];

    /**
     * Replace the entire validation flow with a custom callback.
     *
     * The callback receives the resolved phone, attribute, raw value, the
     * {@see RuleConfig}, and `$fail`, and reports its own errors via `$fail`.
     * Config messages are raw translation keys — wrap in `trans()` when failing.
     * Use this to change how null/empty values are handled.
     *
     * @param  Closure(BasePhone, string, mixed, RuleConfig, Closure(string): mixed): void  $callback
     * @return $this
     */
    public function validateUsing(Closure $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * override the failure message
     *
     * @return $this
     */
    public function message(string $message): static
    {
        $this->formatRule['message'] = $message;

        return $this;
    }

    /**
     * require the phone to be unique in the given table
     *
     * @param  mixed  $ignore  a record id to exclude (e.g. the current user on update)
     * @return $this
     */
    public function unique(string $table, ?string $column = null, mixed $ignore = null, string $ignoreColumn = 'id', ?string $message = null): static
    {
        $this->uniqueRule = [
            'enabled' => true,
            'table' => $table,
            'column' => $column,
            'ignore' => $ignore,
            'ignoreColumn' => $ignoreColumn,
            'message' => $message ?? $this->uniqueRule['message'],
        ];

        return $this;
    }

    /**
     * Make null and empty values fail (undoes nullable()/allowEmpty()).
     *
     * Because the rule is implicit, no separate Laravel `required` rule is
     * needed. Pass false (or a `Closure(): bool` resolving false) to fall back to
     * {@see absent()} instead.
     *
     * @param  bool|Closure(): bool  $condition
     * @return $this
     */
    public function required(bool|Closure $condition = true): static
    {
        if (! $this->resolveCondition($condition)) {
            return $this->absent();
        }

        return $this->allowEmpty(false)->nullable(false);
    }

    /**
     * pass when the value is null, skipping every other check
     *
     * @return $this
     */
    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * pass when the value is an empty string, skipping every other check
     *
     * @return $this
     */
    public function allowEmpty(bool $allowEmpty = true): static
    {
        $this->allowEmpty = $allowEmpty;

        return $this;
    }

    /**
     * Let both null and empty pass, skipping every other check (inverse of
     * required()).
     *
     * Pass false (or a `Closure(): bool` resolving false) to fall back to
     * {@see required()} instead.
     *
     * @param  bool|Closure(): bool  $condition
     * @return $this
     */
    public function absent(bool|Closure $condition = true): static
    {
        if (! $this->resolveCondition($condition)) {
            return $this->required();
        }

        return $this->allowEmpty(true)->nullable(true);
    }

    /**
     * resolve a bool|Closure condition down to a bool
     *
     * @param  bool|Closure(): bool  $condition
     */
    protected function resolveCondition(bool|Closure $condition): bool
    {
        return $condition instanceof Closure ? (bool) $condition() : $condition;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== null && ! is_string($value) && ! is_int($value)) {
            throw new \RuntimeException(
                'Phone number must be a string.'
            );
        }

        $phone = Phone::make((string) $value, $this->countryCode);

        $config = new RuleConfig(
            format: new FormatConfig(
                enabled: true,
                message: $this->formatRule['message'],
            ),
            nullable: new NullableConfig(
                enabled: $this->nullable,
                message: 'phones::validation.required',
            ),
            allowEmpty: new AllowEmptyConfig(
                enabled: $this->allowEmpty,
                message: 'phones::validation.required',
            ),
            exists: new ExistsConfig(
                enabled: $this->existsRule['enabled'],
                table: $this->existsRule['table'],
                column: $this->existsRule['column'],
                message: $this->existsRule['message'],
            ),
            unique: new UniqueConfig(
                enabled: $this->uniqueRule['enabled'],
                table: $this->uniqueRule['table'],
                column: $this->uniqueRule['column'],
                ignore: $this->uniqueRule['ignore'],
                ignoreColumn: $this->uniqueRule['ignoreColumn'],
                message: $this->uniqueRule['message'],
            ),
        );

        if ($this->callback instanceof Closure) {
            ($this->callback)(
                $phone,
                $attribute,
                $value,
                $config,
                $fail,
            );

            return;
        }

        Rules::defaultValidationFunction(
            $phone,
            $attribute,
            $value,
            $config,
            $fail,

        );

    }

    /**
     * Fluent constructor. Takes no code — concrete country rules lock their own;
     * {@see PhoneRule} overrides this to accept an explicit code.
     */
    public static function make(): static
    {
        // @phpstan-ignore new.static
        return new static;
    }

    /**
     * Require the phone to already exist in the given table. Matches every
     * accepted shape, so a value stored in any form (local, international, +, 00)
     * is found.
     *
     * @return $this
     */
    public function exists(string $table, string $column, ?string $message = null): static
    {
        $this->existsRule = [
            'enabled' => true,
            'table' => $table,
            'column' => $column,
            'message' => $message ?? $this->existsRule['message'],
        ];

        return $this;
    }
}
