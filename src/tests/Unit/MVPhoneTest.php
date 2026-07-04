<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MVPhone;
use MMAE\Phones\Placeholders\MVPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MVPhoneRule;

test('can create a phone object', function () {
    expect(MVPhone::make('7000000'))->toBeInstanceOf(MVPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MVPhone::make($number)->isValid())->toBeTrue();
})->with(['9607000000', '9608000000', '9609000000']);

test('is valid with the local key', function () {
    expect(MVPhone::make('7000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MVPhone::make('9607000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MVPhone::make('+9607000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MVPhone::make('009607000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MVPhone::make('9607000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MVPhone::make('9609000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MVPhone::make('960 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('9607000000');
});

test('is not valid when too short', function () {
    expect(MVPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MVPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MVPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MVPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MVPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MVPhone::make('7000000')->all())->toEqual(['+9607000000', '009607000000', '9607000000']);
});

test('toArray mirrors all', function () {
    $phone = MVPhone::make('7000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MVPhone::make('9607000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = MVPhone::make('7000000');
    expect($phone->config('key'))->toEqual('960')
        ->and($phone->config('code'))->toEqual('MV')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MVPhone::make('960 7-000000')->number())->toEqual('960 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MVPhone::make('7000000');
    expect($phone->withPlus()->toString())->toEqual('+9607000000')
        ->and($phone->withoutPlus()->toString())->toEqual('9607000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MVPhone::make('7000000')->toString())->toEqual('+9607000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MVPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => MVPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MVPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => MVPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => MVPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7000000'], ['phone' => MVPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => MVPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MVPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MVPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MV');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MVPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MV')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MVPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
