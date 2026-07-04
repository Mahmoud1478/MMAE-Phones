<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\HUPhone;
use MMAE\Phones\Placeholders\HUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\HUPhoneRule;

test('can create a phone object', function () {
    expect(HUPhone::make('0200000000'))->toBeInstanceOf(HUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(HUPhone::make($number)->isValid())->toBeTrue();
})->with(['36200000000', '36300000000', '36700000000']);

test('is valid with the local key', function () {
    expect(HUPhone::make('0200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(HUPhone::make('36200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(HUPhone::make('+36200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(HUPhone::make('0036200000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(HUPhone::make('36200000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(HUPhone::make('36700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = HUPhone::make('36 2-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('36200000000');
});

test('is not valid when too short', function () {
    expect(HUPhone::make('20000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(HUPhone::make('7000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(HUPhone::make('999200000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(HUPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(HUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(HUPhone::make('0200000000')->all())->toEqual(['+36200000000', '0036200000000', '36200000000', '0200000000']);
});

test('toArray mirrors all', function () {
    $phone = HUPhone::make('0200000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = HUPhone::make('36200000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000000');
});

test('config exposes the country schema', function () {
    $phone = HUPhone::make('0200000000');
    expect($phone->config('key'))->toEqual('36')
        ->and($phone->config('code'))->toEqual('HU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(HUPhone::make('36 2-00000000')->number())->toEqual('36 2-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = HUPhone::make('0200000000');
    expect($phone->withPlus()->toString())->toEqual('+36200000000')
        ->and($phone->withoutPlus()->toString())->toEqual('36200000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(HUPhone::make('0200000000')->toString())->toEqual('+36200000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0200000000'], ['phone' => HUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => HUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(HUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000000'], ['phone' => HUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000000'], ['phone' => HUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0200000000'], ['phone' => HUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => HUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = HUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(HUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('HU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(HUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('HU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(HUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
