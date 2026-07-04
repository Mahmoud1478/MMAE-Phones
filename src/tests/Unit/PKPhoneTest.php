<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PKPhone;
use MMAE\Phones\Placeholders\PKPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\PKPhoneRule;

test('can create a phone object', function () {
    expect(PKPhone::make('03000000000'))->toBeInstanceOf(PKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PKPhone::make($number)->isValid())->toBeTrue();
})->with(['923000000000', '923100000000', '923200000000', '923300000000', '923400000000', '923500000000', '923600000000', '923700000000', '923800000000', '923900000000']);

test('is valid with the local key', function () {
    expect(PKPhone::make('03000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PKPhone::make('923000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PKPhone::make('+923000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PKPhone::make('00923000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PKPhone::make('923000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PKPhone::make('923900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PKPhone::make('92 3-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('923000000000');
});

test('is not valid when too short', function () {
    expect(PKPhone::make('300000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PKPhone::make('39000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PKPhone::make('9993000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PKPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PKPhone::make('03000000000')->all())->toEqual(['+923000000000', '00923000000000', '923000000000', '03000000000']);
});

test('toArray mirrors all', function () {
    $phone = PKPhone::make('03000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PKPhone::make('923000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3000000000');
});

test('config exposes the country schema', function () {
    $phone = PKPhone::make('03000000000');
    expect($phone->config('key'))->toEqual('92')
        ->and($phone->config('code'))->toEqual('PK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PKPhone::make('92 3-000000000')->number())->toEqual('92 3-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PKPhone::make('03000000000');
    expect($phone->withPlus()->toString())->toEqual('+923000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('923000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PKPhone::make('03000000000')->toString())->toEqual('+923000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '03000000000'], ['phone' => PKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => PKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '300000000'], ['phone' => PKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '300000000'], ['phone' => PKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '03000000000'], ['phone' => PKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => PKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
