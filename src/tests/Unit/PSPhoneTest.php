<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PSPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\PSPlaceholder;
use MMAE\Phones\Rules\PSPhoneRule;

test('can create a phone object', function () {
    expect(PSPhone::make('0560000000'))->toBeInstanceOf(PSPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PSPhone::make($number)->isValid())->toBeTrue();
})->with(['970560000000', '970570000000', '970580000000', '970590000000']);

test('is valid with the local key', function () {
    expect(PSPhone::make('0560000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PSPhone::make('970560000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PSPhone::make('+970560000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PSPhone::make('00970560000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PSPhone::make('970560000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PSPhone::make('970590000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PSPhone::make('970 5-60000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('970560000000');
});

test('is not valid when too short', function () {
    expect(PSPhone::make('56000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PSPhone::make('5900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PSPhone::make('999560000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PSPhone::make('0060000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PSPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PSPhone::make('0560000000')->all())->toEqual(['+970560000000', '00970560000000', '970560000000', '0560000000']);
});

test('toArray mirrors all', function () {
    $phone = PSPhone::make('0560000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PSPhone::make('970560000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('560000000');
});

test('config exposes the country schema', function () {
    $phone = PSPhone::make('0560000000');
    expect($phone->config('key'))->toEqual('970')
        ->and($phone->config('code'))->toEqual('PS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PSPhone::make('970 5-60000000')->number())->toEqual('970 5-60000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PSPhone::make('0560000000');
    expect($phone->withPlus()->toString())->toEqual('+970560000000')
        ->and($phone->withoutPlus()->toString())->toEqual('970560000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PSPhone::make('0560000000')->toString())->toEqual('+970560000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0560000000'], ['phone' => PSPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '56000000'], ['phone' => PSPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PSPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '56000000'], ['phone' => PSPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '56000000'], ['phone' => PSPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0560000000'], ['phone' => PSPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '56000000'], ['phone' => PSPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PSPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PSPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PSPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PSPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
