<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\RUPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\RUPlaceholder;
use MMAE\Phones\Rules\RUPhoneRule;

test('can create a phone object', function () {
    expect(RUPhone::make('89000000000'))->toBeInstanceOf(RUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(RUPhone::make($number)->isValid())->toBeTrue();
})->with(['79000000000', '79100000000', '79200000000', '79300000000', '79400000000', '79500000000', '79600000000', '79700000000', '79800000000', '79900000000']);

test('is valid with the local key', function () {
    expect(RUPhone::make('89000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(RUPhone::make('79000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(RUPhone::make('+79000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(RUPhone::make('0079000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(RUPhone::make('79000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(RUPhone::make('79900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = RUPhone::make('7 9-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('79000000000');
});

test('is not valid when too short', function () {
    expect(RUPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(RUPhone::make('99000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(RUPhone::make('9999000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(RUPhone::make('80000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(RUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(RUPhone::make('89000000000')->all())->toEqual(['+79000000000', '0079000000000', '79000000000', '89000000000']);
});

test('toArray mirrors all', function () {
    $phone = RUPhone::make('89000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = RUPhone::make('79000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('9000000000');
});

test('config exposes the country schema', function () {
    $phone = RUPhone::make('89000000000');
    expect($phone->config('key'))->toEqual('7')
        ->and($phone->config('code'))->toEqual('RU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(RUPhone::make('7 9-000000000')->number())->toEqual('7 9-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = RUPhone::make('89000000000');
    expect($phone->withPlus()->toString())->toEqual('+79000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('79000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(RUPhone::make('89000000000')->toString())->toEqual('+79000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '89000000000'], ['phone' => RUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => RUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(RUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '900000000'], ['phone' => RUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '900000000'], ['phone' => RUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '89000000000'], ['phone' => RUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => RUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = RUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(RUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('RU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(RUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('RU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(RUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
