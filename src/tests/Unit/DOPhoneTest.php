<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\DOPhone;
use MMAE\Phones\Placeholders\DOPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\DOPhoneRule;

test('can create a phone object', function () {
    expect(DOPhone::make('18000000000'))->toBeInstanceOf(DOPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(DOPhone::make($number)->isValid())->toBeTrue();
})->with(['18000000000', '18010000000', '18020000000', '18030000000', '18040000000', '18050000000', '18060000000', '18070000000', '18080000000', '18090000000', '18100000000', '18110000000', '18120000000', '18130000000', '18140000000', '18150000000', '18160000000', '18170000000', '18180000000', '18190000000', '18200000000', '18210000000', '18220000000', '18230000000', '18240000000', '18250000000', '18260000000', '18270000000', '18280000000', '18290000000', '18300000000', '18310000000']);

test('is valid with the local key', function () {
    expect(DOPhone::make('18000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(DOPhone::make('18000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(DOPhone::make('+18000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(DOPhone::make('0018000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(DOPhone::make('18000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(DOPhone::make('18310000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = DOPhone::make('1 8-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('18000000000');
});

test('is not valid when too short', function () {
    expect(DOPhone::make('800000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(DOPhone::make('83100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(DOPhone::make('9998000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(DOPhone::make('10000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(DOPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(DOPhone::make('18000000000')->all())->toEqual(['+18000000000', '0018000000000', '18000000000']);
});

test('toArray mirrors all', function () {
    $phone = DOPhone::make('18000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = DOPhone::make('18000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('8000000000');
});

test('config exposes the country schema', function () {
    $phone = DOPhone::make('18000000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('DO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(DOPhone::make('1 8-000000000')->number())->toEqual('1 8-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = DOPhone::make('18000000000');
    expect($phone->withPlus()->toString())->toEqual('+18000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('18000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(DOPhone::make('18000000000')->toString())->toEqual('+18000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '18000000000'], ['phone' => DOPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '800000000'], ['phone' => DOPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(DOPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '800000000'], ['phone' => DOPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '800000000'], ['phone' => DOPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '18000000000'], ['phone' => DOPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '800000000'], ['phone' => DOPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = DOPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(DOPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('DO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(DOPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('DO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(DOPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
