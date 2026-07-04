<?php

namespace MMAE\Phones\Defaults;

use Closure;
use Illuminate\Support\Facades\DB;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Base\BasePhoneRule;
use MMAE\Phones\Configs\RuleConfig;
use MMAE\Phones\Placeholders\Placeholder;

/**
 * Default validation flow shared by every {@see BasePhoneRule}.
 *
 * Applies the resolved {@see RuleConfig} in order: nullable/allowEmpty
 * short-circuits, format check, then optional exists/unique DB checks (matched
 * against every accepted shape via {@see BasePhone::all()}). Failures go through
 * `$fail` with translated messages. Bypassed when the rule sets a
 * {@see BasePhoneRule::validateUsing()} callback.
 */
class Rules
{
    /**
     * Run the default format / exists / unique flow, failing via `$fail`.
     *
     * @param  Closure(string, ?string=): mixed  $fail
     */
    public static function defaultValidationFunction(
        BasePhone $phone,
        string $attribute,
        mixed $value,
        RuleConfig $rules,
        Closure $fail
    ): void {

        if ($rules->nullable->enabled && $value === null) {
            return;
        }
        if ($value === null) {
            $fail(trans($rules->nullable->message));

            return;
        }

        if ($rules->allowEmpty->enabled && $value === '') {
            return;
        }

        if ($value === '') {
            $fail(trans($rules->allowEmpty->message));

            return;
        }

        if (! $phone->isValid()) {
            $fail(trans($rules->format->message, ['format' => self::formatExample($phone)]));

            return;
        }

        $shapes = $phone->all();

        if ($rules->exists->enabled && ! DB::table($rules->exists->table)->whereIn($rules->exists->column ?: $attribute, $shapes)->exists()) {
            $fail(trans($rules->exists->message));

            return;
        }

        if ($rules->unique->enabled) {
            $exists = DB::table($rules->unique->table)->whereIn($rules->unique->column ?: $attribute, $shapes)
                ->when($rules->unique->ignore, function ($query) use ($rules) {
                    $query->where($rules->unique->ignoreColumn, '<>', $rules->unique->ignore);
                })->exists();

            if ($exists) {
                $fail(trans($rules->unique->message));
            }
        }
    }

    /**
     * The accepted local format for the phone's country, e.g.
     * `01[0,1,2,5]XXXXXXXX`. Empty when the country is unknown.
     */
    private static function formatExample(BasePhone $phone): string
    {
        $code = $phone->config('code');
        if (! is_string($code) || $code === '') {
            return '';
        }

        return Placeholder::make($code)->extract()->localFormat();
    }
}
