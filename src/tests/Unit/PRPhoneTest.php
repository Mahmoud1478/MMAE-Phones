<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PRPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\PRPlaceholder;
use MMAE\Phones\Rules\PRPhoneRule;

test('can create a phone object', function () {
    expect(PRPhone::make('17870000000'))->toBeInstanceOf(PRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PRPhone::make($number)->isValid())->toBeTrue();
})->with(['17870000000', '19390000000']);

test('is valid with the local key', function () {
    expect(PRPhone::make('17870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PRPhone::make('17870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PRPhone::make('+17870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PRPhone::make('0017870000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PRPhone::make('17870000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PRPhone::make('19390000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PRPhone::make('1 7-870000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('17870000000');
});

test('is not valid when too short', function () {
    expect(PRPhone::make('787000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PRPhone::make('93900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PRPhone::make('9997870000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PRPhone::make('10870000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PRPhone::make('17870000000')->all())->toEqual(['+17870000000', '0017870000000', '17870000000']);
});

test('toArray mirrors all', function () {
    $phone = PRPhone::make('17870000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PRPhone::make('17870000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7870000000');
});

test('config exposes the country schema', function () {
    $phone = PRPhone::make('17870000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('PR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PRPhone::make('1 7-870000000')->number())->toEqual('1 7-870000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PRPhone::make('17870000000');
    expect($phone->withPlus()->toString())->toEqual('+17870000000')
        ->and($phone->withoutPlus()->toString())->toEqual('17870000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PRPhone::make('17870000000')->toString())->toEqual('+17870000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17870000000'], ['phone' => PRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '787000000'], ['phone' => PRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '787000000'], ['phone' => PRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '787000000'], ['phone' => PRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17870000000'], ['phone' => PRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '787000000'], ['phone' => PRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
