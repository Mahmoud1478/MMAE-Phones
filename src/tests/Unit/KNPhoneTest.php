<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KNPhone;
use MMAE\Phones\Placeholders\KNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KNPhoneRule;

test('can create a phone object', function () {
    expect(KNPhone::make('18690000000'))->toBeInstanceOf(KNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KNPhone::make($number)->isValid())->toBeTrue();
})->with(['18690000000']);

test('is valid with the local key', function () {
    expect(KNPhone::make('18690000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KNPhone::make('18690000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KNPhone::make('+18690000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KNPhone::make('0018690000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KNPhone::make('18690000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KNPhone::make('18690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KNPhone::make('1 8-690000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('18690000000');
});

test('is not valid when too short', function () {
    expect(KNPhone::make('869000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KNPhone::make('86900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KNPhone::make('9998690000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KNPhone::make('10690000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KNPhone::make('18690000000')->all())->toEqual(['+18690000000', '0018690000000', '18690000000']);
});

test('toArray mirrors all', function () {
    $phone = KNPhone::make('18690000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KNPhone::make('18690000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('8690000000');
});

test('config exposes the country schema', function () {
    $phone = KNPhone::make('18690000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('KN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KNPhone::make('1 8-690000000')->number())->toEqual('1 8-690000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KNPhone::make('18690000000');
    expect($phone->withPlus()->toString())->toEqual('+18690000000')
        ->and($phone->withoutPlus()->toString())->toEqual('18690000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KNPhone::make('18690000000')->toString())->toEqual('+18690000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '18690000000'], ['phone' => KNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '869000000'], ['phone' => KNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '869000000'], ['phone' => KNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '869000000'], ['phone' => KNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '18690000000'], ['phone' => KNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '869000000'], ['phone' => KNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
