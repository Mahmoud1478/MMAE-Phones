<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SXPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SXPlaceholder;
use MMAE\Phones\Rules\SXPhoneRule;

test('can create a phone object', function () {
    expect(SXPhone::make('17210000000'))->toBeInstanceOf(SXPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SXPhone::make($number)->isValid())->toBeTrue();
})->with(['17210000000']);

test('is valid with the local key', function () {
    expect(SXPhone::make('17210000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SXPhone::make('17210000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SXPhone::make('+17210000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SXPhone::make('0017210000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SXPhone::make('17210000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SXPhone::make('17210000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SXPhone::make('1 7-210000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('17210000000');
});

test('is not valid when too short', function () {
    expect(SXPhone::make('721000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SXPhone::make('72100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SXPhone::make('9997210000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SXPhone::make('10210000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SXPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SXPhone::make('17210000000')->all())->toEqual(['+17210000000', '0017210000000', '17210000000']);
});

test('toArray mirrors all', function () {
    $phone = SXPhone::make('17210000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SXPhone::make('17210000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7210000000');
});

test('config exposes the country schema', function () {
    $phone = SXPhone::make('17210000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('SX')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SXPhone::make('1 7-210000000')->number())->toEqual('1 7-210000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SXPhone::make('17210000000');
    expect($phone->withPlus()->toString())->toEqual('+17210000000')
        ->and($phone->withoutPlus()->toString())->toEqual('17210000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SXPhone::make('17210000000')->toString())->toEqual('+17210000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17210000000'], ['phone' => SXPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '721000000'], ['phone' => SXPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SXPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '721000000'], ['phone' => SXPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '721000000'], ['phone' => SXPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17210000000'], ['phone' => SXPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '721000000'], ['phone' => SXPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SXPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SXPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SX');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SXPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SX')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SXPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
