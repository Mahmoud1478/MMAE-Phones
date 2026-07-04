<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KMPhone;
use MMAE\Phones\Placeholders\KMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KMPhoneRule;

test('can create a phone object', function () {
    expect(KMPhone::make('3200000'))->toBeInstanceOf(KMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KMPhone::make($number)->isValid())->toBeTrue();
})->with(['2693200000', '2693300000', '2693400000']);

test('is valid with the local key', function () {
    expect(KMPhone::make('3200000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KMPhone::make('2693200000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KMPhone::make('+2693200000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KMPhone::make('002693200000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KMPhone::make('2693200000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KMPhone::make('2693400000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KMPhone::make('269 3-200000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2693200000');
});

test('is not valid when too short', function () {
    expect(KMPhone::make('320000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KMPhone::make('34000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KMPhone::make('9993200000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KMPhone::make('0200000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KMPhone::make('3200000')->all())->toEqual(['+2693200000', '002693200000', '2693200000']);
});

test('toArray mirrors all', function () {
    $phone = KMPhone::make('3200000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KMPhone::make('2693200000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3200000');
});

test('config exposes the country schema', function () {
    $phone = KMPhone::make('3200000');
    expect($phone->config('key'))->toEqual('269')
        ->and($phone->config('code'))->toEqual('KM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KMPhone::make('269 3-200000')->number())->toEqual('269 3-200000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KMPhone::make('3200000');
    expect($phone->withPlus()->toString())->toEqual('+2693200000')
        ->and($phone->withoutPlus()->toString())->toEqual('2693200000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KMPhone::make('3200000')->toString())->toEqual('+2693200000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '3200000'], ['phone' => KMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '320000'], ['phone' => KMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '320000'], ['phone' => KMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '320000'], ['phone' => KMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '3200000'], ['phone' => KMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '320000'], ['phone' => KMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
