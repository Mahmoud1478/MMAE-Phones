<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\WSPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\WSPlaceholder;
use MMAE\Phones\Rules\WSPhoneRule;

test('can create a phone object', function () {
    expect(WSPhone::make('7200000'))->toBeInstanceOf(WSPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(WSPhone::make($number)->isValid())->toBeTrue();
})->with(['6857200000', '6857300000', '6857400000', '6857500000', '6857600000', '6857700000', '6857800000']);

test('is valid with the local key', function () {
    expect(WSPhone::make('7200000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(WSPhone::make('6857200000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(WSPhone::make('+6857200000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(WSPhone::make('006857200000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(WSPhone::make('6857200000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(WSPhone::make('6857800000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = WSPhone::make('685 7-200000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6857200000');
});

test('is not valid when too short', function () {
    expect(WSPhone::make('720000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(WSPhone::make('78000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(WSPhone::make('9997200000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(WSPhone::make('0200000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(WSPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(WSPhone::make('7200000')->all())->toEqual(['+6857200000', '006857200000', '6857200000']);
});

test('toArray mirrors all', function () {
    $phone = WSPhone::make('7200000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = WSPhone::make('6857200000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7200000');
});

test('config exposes the country schema', function () {
    $phone = WSPhone::make('7200000');
    expect($phone->config('key'))->toEqual('685')
        ->and($phone->config('code'))->toEqual('WS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(WSPhone::make('685 7-200000')->number())->toEqual('685 7-200000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = WSPhone::make('7200000');
    expect($phone->withPlus()->toString())->toEqual('+6857200000')
        ->and($phone->withoutPlus()->toString())->toEqual('6857200000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(WSPhone::make('7200000')->toString())->toEqual('+6857200000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7200000'], ['phone' => WSPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '720000'], ['phone' => WSPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(WSPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '720000'], ['phone' => WSPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '720000'], ['phone' => WSPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7200000'], ['phone' => WSPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '720000'], ['phone' => WSPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = WSPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(WSPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('WS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(WSPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('WS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(WSPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
