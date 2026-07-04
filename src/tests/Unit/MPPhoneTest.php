<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MPPhone;
use MMAE\Phones\Placeholders\MPPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MPPhoneRule;

test('can create a phone object', function () {
    expect(MPPhone::make('16700000000'))->toBeInstanceOf(MPPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MPPhone::make($number)->isValid())->toBeTrue();
})->with(['16700000000']);

test('is valid with the local key', function () {
    expect(MPPhone::make('16700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MPPhone::make('16700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MPPhone::make('+16700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MPPhone::make('0016700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MPPhone::make('16700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MPPhone::make('16700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MPPhone::make('1 6-700000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16700000000');
});

test('is not valid when too short', function () {
    expect(MPPhone::make('670000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MPPhone::make('67000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MPPhone::make('9996700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MPPhone::make('10700000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MPPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MPPhone::make('16700000000')->all())->toEqual(['+16700000000', '0016700000000', '16700000000']);
});

test('toArray mirrors all', function () {
    $phone = MPPhone::make('16700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MPPhone::make('16700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6700000000');
});

test('config exposes the country schema', function () {
    $phone = MPPhone::make('16700000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('MP')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MPPhone::make('1 6-700000000')->number())->toEqual('1 6-700000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MPPhone::make('16700000000');
    expect($phone->withPlus()->toString())->toEqual('+16700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MPPhone::make('16700000000')->toString())->toEqual('+16700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16700000000'], ['phone' => MPPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '670000000'], ['phone' => MPPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MPPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '670000000'], ['phone' => MPPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '670000000'], ['phone' => MPPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16700000000'], ['phone' => MPPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '670000000'], ['phone' => MPPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MPPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MPPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MP');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MPPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MP')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MPPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
