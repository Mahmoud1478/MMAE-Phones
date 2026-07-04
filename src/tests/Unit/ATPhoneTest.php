<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ATPhone;
use MMAE\Phones\Placeholders\ATPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ATPhoneRule;

test('can create a phone object', function () {
    expect(ATPhone::make('06400000'))->toBeInstanceOf(ATPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ATPhone::make($number)->isValid())->toBeTrue();
})->with(['436400000', '436500000', '436600000', '436700000', '436800000', '436900000', '436400000000000', '436500000000000', '436600000000000', '436700000000000', '436800000000000', '436900000000000']);

test('is valid with the local key', function () {
    expect(ATPhone::make('06400000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ATPhone::make('436400000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ATPhone::make('+436400000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ATPhone::make('00436400000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ATPhone::make('436400000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ATPhone::make('436900000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ATPhone::make('43 6-400000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('436400000');
});

test('is not valid when too short', function () {
    expect(ATPhone::make('640000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ATPhone::make('69000000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ATPhone::make('9996400000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ATPhone::make('00400000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ATPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ATPhone::make('06400000')->all())->toEqual(['+436400000', '00436400000', '436400000', '06400000']);
});

test('toArray mirrors all', function () {
    $phone = ATPhone::make('06400000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ATPhone::make('436400000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6400000');
});

test('config exposes the country schema', function () {
    $phone = ATPhone::make('06400000');
    expect($phone->config('key'))->toEqual('43')
        ->and($phone->config('code'))->toEqual('AT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ATPhone::make('43 6-400000')->number())->toEqual('43 6-400000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ATPhone::make('06400000');
    expect($phone->withPlus()->toString())->toEqual('+436400000')
        ->and($phone->withoutPlus()->toString())->toEqual('436400000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ATPhone::make('06400000')->toString())->toEqual('+436400000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '06400000'], ['phone' => ATPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '640000'], ['phone' => ATPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ATPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '640000'], ['phone' => ATPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '640000'], ['phone' => ATPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '06400000'], ['phone' => ATPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '640000'], ['phone' => ATPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ATPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ATPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ATPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ATPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
