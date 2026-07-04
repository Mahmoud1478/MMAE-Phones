<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\EGPhone;
use MMAE\Phones\Placeholders\EGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\EGPhoneRule;

test('can create a phone object', function () {
    expect(EGPhone::make('01000000000'))->toBeInstanceOf(EGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(EGPhone::make($number)->isValid())->toBeTrue();
})->with(['201000000000', '201100000000', '201200000000', '201500000000']);

test('is valid with the local key', function () {
    expect(EGPhone::make('01000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(EGPhone::make('201000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(EGPhone::make('+201000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(EGPhone::make('00201000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(EGPhone::make('201000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(EGPhone::make('201500000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = EGPhone::make('20 1-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('201000000000');
});

test('is not valid when too short', function () {
    expect(EGPhone::make('100000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(EGPhone::make('15000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(EGPhone::make('9991000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(EGPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(EGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(EGPhone::make('01000000000')->all())->toEqual(['+201000000000', '00201000000000', '201000000000', '01000000000']);
});

test('toArray mirrors all', function () {
    $phone = EGPhone::make('01000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = EGPhone::make('201000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('1000000000');
});

test('config exposes the country schema', function () {
    $phone = EGPhone::make('01000000000');
    expect($phone->config('key'))->toEqual('20')
        ->and($phone->config('code'))->toEqual('EG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(EGPhone::make('20 1-000000000')->number())->toEqual('20 1-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = EGPhone::make('01000000000');
    expect($phone->withPlus()->toString())->toEqual('+201000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('201000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(EGPhone::make('01000000000')->toString())->toEqual('+201000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '100000000'], ['phone' => EGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(EGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '100000000'], ['phone' => EGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '100000000'], ['phone' => EGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '100000000'], ['phone' => EGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('format failure tells the user the expected placeholder', function () {
    $validator = Validator::make(['phone' => '100000000'], ['phone' => EGPhoneRule::make()]);
    expect($validator->errors()->first('phone'))->toContain('01[0,1,2,5]XXXXXXXX');
});

test('placeholder is locked to the country code', function () {
    $placeholder = EGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(EGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('EG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(EGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('EG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(EGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
