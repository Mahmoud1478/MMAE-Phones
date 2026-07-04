<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Configs\RuleConfig;
use MMAE\Phones\Rules\EGPhoneRule;
use MMAE\Phones\Rules\PhoneRule;

test('passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toEqual('The phone is not a valid phone number. Expected format: 01[0,1,2,5]XXXXXXXX.');
});

test('throws for a non-string, non-int value', function () {
    expect(fn () => Validator::make(['phone' => ['array']], ['phone' => EGPhoneRule::make()])->fails())
        ->toThrow(RuntimeException::class);
});

test('message can be overridden', function () {
    $validator = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('callback takes full control of the flow', function () {
    // callback ignores the format check and passes an otherwise invalid number
    $pass = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    // callback fails an otherwise valid number via $fail
    $fail = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->validateUsing(
        fn ($phone, $attribute, $value, $config, $fail) => $fail('nope')
    )]);
    expect($fail->fails())->toBeTrue()
        ->and($fail->errors()->first('phone'))->toEqual('nope');
});

test('callback receives the phone, attribute, value, config and fail', function () {
    $seen = null;
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')->validateUsing(
        function (BasePhone $phone, string $attribute, mixed $value, RuleConfig $config, Closure $fail) use (&$seen) {
            $seen = [
                'attribute' => $attribute,
                'value' => $value,
                'existsEnabled' => $config->exists->enabled,
                'existsTable' => $config->exists->table,
                'formatMessage' => $config->format->message,
            ];

            if ($phone->isNotValid()) {
                $fail(trans($config->format->message));
            }
        }
    )]);

    expect($validator->passes())->toBeTrue()
        ->and($seen)->toEqual([
            'attribute' => 'phone',
            'value' => '01000000000',
            'existsEnabled' => true,
            'existsTable' => 'users',
            'formatMessage' => 'phones::validation.phone',
        ]);
});

test('nullable passes on null and is off by default', function () {
    $off = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()]);
    expect($off->fails())->toBeTrue();

    $on = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->nullable()]);
    expect($on->passes())->toBeTrue();
});

test('nullable still validates a present value', function () {
    $validator = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()->nullable()]);
    expect($validator->fails())->toBeTrue();
});

test('allowEmpty short-circuits an empty string at the rule level', function () {
    // driven directly to exercise the flag in isolation of the Validator
    $failedWithout = false;
    EGPhoneRule::make()->validate('phone', '', function () use (&$failedWithout) {
        $failedWithout = true;
    });
    expect($failedWithout)->toBeTrue();

    $failedWith = false;
    EGPhoneRule::make()->allowEmpty()->validate('phone', '', function () use (&$failedWith) {
        $failedWith = true;
    });
    expect($failedWith)->toBeFalse();
});

test('required undoes nullable and allowEmpty', function () {
    // driven directly to exercise the flags in isolation of the Validator
    $nullFailed = false;
    EGPhoneRule::make()->nullable()->required()->validate('phone', null, function () use (&$nullFailed) {
        $nullFailed = true;
    });
    expect($nullFailed)->toBeTrue();

    $emptyMessage = null;
    EGPhoneRule::make()->allowEmpty()->required()->validate('phone', '', function ($message) use (&$emptyMessage) {
        $emptyMessage = $message;
    });
    expect($emptyMessage)->toEqual('The :attribute field is required.');
});

test('required(false) treats the value as absent', function () {
    $bool = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->required(false)]);
    expect($bool->passes())->toBeTrue();

    $closure = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->required(fn () => false)]);
    expect($closure->passes())->toBeTrue();
});

test('required(true) keeps the value required', function () {
    $bool = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->nullable()->required(true)]);
    expect($bool->fails())->toBeTrue();

    $closure = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->nullable()->required(fn () => true)]);
    expect($closure->fails())->toBeTrue();
});

test('absent(false) makes the value required', function () {
    $bool = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->absent(false)]);
    expect($bool->fails())->toBeTrue();

    $closure = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->absent(fn () => false)]);
    expect($closure->fails())->toBeTrue();
});

test('absent(true) skips null and empty', function () {
    $bool = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->absent(true)]);
    expect($bool->passes())->toBeTrue();

    $closure = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()->absent(fn () => true)]);
    expect($closure->passes())->toBeTrue();

    // empty string is short-circuited at the rule level
    $emptyFailed = false;
    EGPhoneRule::make()->absent(fn () => true)->validate('phone', '', function () use (&$emptyFailed) {
        $emptyFailed = true;
    });
    expect($emptyFailed)->toBeFalse();
});

test('rule is implicit so an absent key is still validated', function () {
    // non-implicit rules are skipped on missing keys; this one runs and fails
    $validator = Validator::make([], ['phone' => EGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toEqual('The phone field is required.');

    // nullable() lets the absent key pass without a separate `required` rule
    $nullable = Validator::make([], ['phone' => EGPhoneRule::make()->nullable()]);
    expect($nullable->passes())->toBeTrue();
});

test('rule is implicit so an empty string is still validated', function () {
    $validator = Validator::make(['phone' => ''], ['phone' => EGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toEqual('The phone field is required.');

    $allowed = Validator::make(['phone' => ''], ['phone' => EGPhoneRule::make()->allowEmpty()]);
    expect($allowed->passes())->toBeTrue();
});

test('required message is reported through the validator with the attribute filled', function () {
    // the rule is implicit, so null reaches it and fails with the required message
    $validator = Validator::make(['phone' => null], ['phone' => EGPhoneRule::make()]);
    expect($validator->errors()->first('phone'))->toEqual('The phone field is required.');
});

test('default message is translated from the package lang files', function () {
    $en = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()]);
    expect($en->errors()->first('phone'))->toEqual('The phone is not a valid phone number. Expected format: 01[0,1,2,5]XXXXXXXX.');

    app()->setLocale('ar');
    $ar = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()]);
    expect($ar->errors()->first('phone'))->toEqual('phone ليس رقم هاتف صحيح. الصيغة الصحيحة: 01[0,1,2,5]XXXXXXXX.');
    app()->setLocale('en');
});

test('country rules lock their locale and expose no code setter', function () {
    // extra args to make() are ignored, and there is no for() mutator
    expect(method_exists(EGPhoneRule::class, 'for'))->toBeFalse();

    $validator = Validator::make(['phone' => '+966500000000'], ['phone' => EGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue(); // still validated as EG, not SA
});

test('generic rule throws without a country code', function () {
    PhoneRule::make();
})->throws(InvalidArgumentException::class);

test('generic rule takes an explicit country code', function (string $code, string $number, bool $passes) {
    $validator = Validator::make(['phone' => $number], ['phone' => PhoneRule::make($code)]);
    expect($validator->passes())->toBe($passes);
})->with([
    'EG valid' => ['EG', '01000000000', true],
    'SA valid' => ['SA', '+966500000000', true],
    'EG number under SA' => ['SA', '01000000000', false],
]);

test('generic rule still honours the callback override', function () {
    $validator = Validator::make(['phone' => '999'], ['phone' => PhoneRule::make('EG')->validateUsing(fn () => true)]);
    expect($validator->passes())->toBeTrue();
});
