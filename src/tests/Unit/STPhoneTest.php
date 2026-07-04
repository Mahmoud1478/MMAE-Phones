<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\STPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\STPlaceholder;
use MMAE\Phones\Rules\STPhoneRule;

test('can create a phone object', function () {
    expect(STPhone::make('9000000'))->toBeInstanceOf(STPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(STPhone::make($number)->isValid())->toBeTrue();
})->with(['2399000000']);

test('is valid with the local key', function () {
    expect(STPhone::make('9000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(STPhone::make('2399000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(STPhone::make('+2399000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(STPhone::make('002399000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(STPhone::make('2399000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(STPhone::make('2399000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = STPhone::make('239 9-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2399000000');
});

test('is not valid when too short', function () {
    expect(STPhone::make('900000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(STPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(STPhone::make('9999000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(STPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(STPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(STPhone::make('9000000')->all())->toEqual(['+2399000000', '002399000000', '2399000000']);
});

test('toArray mirrors all', function () {
    $phone = STPhone::make('9000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = STPhone::make('2399000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('9000000');
});

test('config exposes the country schema', function () {
    $phone = STPhone::make('9000000');
    expect($phone->config('key'))->toEqual('239')
        ->and($phone->config('code'))->toEqual('ST')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(STPhone::make('239 9-000000')->number())->toEqual('239 9-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = STPhone::make('9000000');
    expect($phone->withPlus()->toString())->toEqual('+2399000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2399000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(STPhone::make('9000000')->toString())->toEqual('+2399000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => STPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '900000'], ['phone' => STPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(STPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '900000'], ['phone' => STPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '900000'], ['phone' => STPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '9000000'], ['phone' => STPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '900000'], ['phone' => STPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = STPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(STPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ST');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(STPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ST')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(STPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
