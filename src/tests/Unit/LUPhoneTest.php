<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LUPhone;
use MMAE\Phones\Placeholders\LUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LUPhoneRule;

test('can create a phone object', function () {
    expect(LUPhone::make('60000000'))->toBeInstanceOf(LUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LUPhone::make($number)->isValid())->toBeTrue();
})->with(['35260000000', '352600000000']);

test('is valid with the local key', function () {
    expect(LUPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LUPhone::make('35260000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LUPhone::make('+35260000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LUPhone::make('0035260000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LUPhone::make('35260000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LUPhone::make('352600000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LUPhone::make('352 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('35260000000');
});

test('is not valid when too short', function () {
    expect(LUPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LUPhone::make('6000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LUPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LUPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LUPhone::make('60000000')->all())->toEqual(['+35260000000', '0035260000000', '35260000000']);
});

test('toArray mirrors all', function () {
    $phone = LUPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LUPhone::make('35260000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = LUPhone::make('60000000');
    expect($phone->config('key'))->toEqual('352')
        ->and($phone->config('code'))->toEqual('LU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LUPhone::make('352 6-0000000')->number())->toEqual('352 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LUPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+35260000000')
        ->and($phone->withoutPlus()->toString())->toEqual('35260000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LUPhone::make('60000000')->toString())->toEqual('+35260000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => LUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => LUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => LUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => LUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => LUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => LUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
