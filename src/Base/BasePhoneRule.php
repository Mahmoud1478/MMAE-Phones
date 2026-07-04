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
 * Base class for every country phone-number validation rule.
 *
 * Implements Laravel's ValidationRule and marks itself implicit, so null,
 * empty, and absent values still reach the rule and are handled by its own
 * nullable()/allowEmpty()/required() logic instead of being skipped.
 *
 * The default flow runs a format check first (the database is never queried
 * for an invalid number), then optional {@see exists()} / {@see unique()}
 * checks matched against every accepted shape of the number
 * ({@see BasePhone::all()}). Messages are `phones::` translation keys. The
 * whole API is fluent and chainable; {@see validateUsing()} replaces the flow
 * entirely.
 *
 * Concrete country rules ({@see EGPhoneRule}, ...) lock
 * their code; the generic {@see PhoneRule} takes an
 * explicit code.
 */
abstract class BasePhoneRule implements ValidationRule
{
    /**
     * mark the rule implicit so Laravel's Validator still runs it on null,
     * empty, and absent values instead of skipping it; the rule then applies
     * its own nullable()/allowEmpty()/required() logic to those values.
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
     * full-flow override; once set it replaces the built-in format/exists/unique
     * flow, so it is not tied to the format check alone
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
     * take full control of the validation flow
     *
     * once set, the callback replaces the built-in format/exists/unique flow.
     * it receives the resolved phone, the attribute, the raw value, the rule
     * config (a RuleConfig), and the `$fail` closure so the caller decides which
     * checks to enforce and reports its own errors via `$fail`. it returns
     * nothing. config messages are raw keys — `trans()` them when failing.
     *
     * the default flow stops (passes) on null when nullable() is set and on an
     * empty string when allowEmpty() is set, otherwise it fails them; write a
     * callback here to change how null/empty values are handled.
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
     * undo nullable()/allowEmpty() so null and empty are validated (and fail)
     *
     * accepts a bool or a `Closure(): bool` to toggle conditionally: when it
     * resolves true the value is required, when false it is treated as absent().
     *
     * the rule is implicit, so null, empty, and absent values all reach it and
     * fail here — no separate Laravel `required` rule is needed. to keep the
     * value required but handle null/empty differently, pass a callback to
     * validateUsing().
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
     * mirror of required(): allow both null and empty to skip every check
     *
     * accepts a bool or a `Closure(): bool` to toggle conditionally: when it
     * resolves true the value is treated as absent, when false it is required().
     *
     * with the default flow a null/empty value then passes and every other
     * check is skipped; to short-circuit on different values, pass a callback
     * to validateUsing().
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
     * fluent constructor
     *
     * takes no country code on purpose: concrete country rules lock their
     * locale via the fixed $countryCode property and it must not be swappable.
     * the generic PhoneRule accepts a code through its own constructor.
     */
    public static function make(): static
    {
        // safe: concrete country rules keep the default constructor; the only
        // subclass with a required-arg constructor (PhoneRule) overrides make().
        // @phpstan-ignore new.static
        return new static;
    }

    /**
     * require the phone to already exist in the given table
     *
     * every accepted shape of the number is matched, so a value stored in any
     * form (local, international, +, 00) is found.
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
