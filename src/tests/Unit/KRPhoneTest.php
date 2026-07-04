<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KRPhone;
use MMAE\Phones\Placeholders\KRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KRPhoneRule;

test('can create a phone object', function () {
    expect(KRPhone::make('01000000000'))->toBeInstanceOf(KRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KRPhone::make($number)->isValid())->toBeTrue();
})->with(['821000000000', '821100000000', '821200000000', '821300000000', '821400000000', '821500000000', '821600000000', '821700000000', '821800000000', '821900000000']);

test('is valid with the local key', function () {
    expect(KRPhone::make('01000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KRPhone::make('821000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KRPhone::make('+821000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KRPhone::make('00821000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KRPhone::make('821000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KRPhone::make('821900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KRPhone::make('82 1-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('821000000000');
});

test('is not valid when too short', function () {
    expect(KRPhone::make('100000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KRPhone::make('19000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KRPhone::make('9991000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KRPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KRPhone::make('01000000000')->all())->toEqual(['+821000000000', '00821000000000', '821000000000', '01000000000']);
});

test('toArray mirrors all', function () {
    $phone = KRPhone::make('01000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KRPhone::make('821000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('1000000000');
});

test('config exposes the country schema', function () {
    $phone = KRPhone::make('01000000000');
    expect($phone->config('key'))->toEqual('82')
        ->and($phone->config('code'))->toEqual('KR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KRPhone::make('82 1-000000000')->number())->toEqual('82 1-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KRPhone::make('01000000000');
    expect($phone->withPlus()->toString())->toEqual('+821000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('821000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KRPhone::make('01000000000')->toString())->toEqual('+821000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => KRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '100000000'], ['phone' => KRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '100000000'], ['phone' => KRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '100000000'], ['phone' => KRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '01000000000'], ['phone' => KRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '100000000'], ['phone' => KRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
