<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KZPhone;
use MMAE\Phones\Placeholders\KZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KZPhoneRule;

test('can create a phone object', function () {
    expect(KZPhone::make('87000000000'))->toBeInstanceOf(KZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KZPhone::make($number)->isValid())->toBeTrue();
})->with(['77000000000', '77110000000', '77220000000', '77330000000', '77440000000', '77550000000', '77660000000', '77770000000', '77880000000', '77990000000']);

test('is valid with the local key', function () {
    expect(KZPhone::make('87000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KZPhone::make('77000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KZPhone::make('+77000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KZPhone::make('0077000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KZPhone::make('77000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KZPhone::make('77990000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KZPhone::make('7 7-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('77000000000');
});

test('is not valid when too short', function () {
    expect(KZPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KZPhone::make('79900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KZPhone::make('9997000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KZPhone::make('80000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KZPhone::make('87000000000')->all())->toEqual(['+77000000000', '0077000000000', '77000000000', '87000000000']);
});

test('toArray mirrors all', function () {
    $phone = KZPhone::make('87000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KZPhone::make('77000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000000');
});

test('config exposes the country schema', function () {
    $phone = KZPhone::make('87000000000');
    expect($phone->config('key'))->toEqual('7')
        ->and($phone->config('code'))->toEqual('KZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KZPhone::make('7 7-000000000')->number())->toEqual('7 7-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KZPhone::make('87000000000');
    expect($phone->withPlus()->toString())->toEqual('+77000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('77000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KZPhone::make('87000000000')->toString())->toEqual('+77000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '87000000000'], ['phone' => KZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => KZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000000'], ['phone' => KZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000000'], ['phone' => KZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '87000000000'], ['phone' => KZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => KZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
