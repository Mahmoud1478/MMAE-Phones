<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LSPhone;
use MMAE\Phones\Placeholders\LSPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LSPhoneRule;

test('can create a phone object', function () {
    expect(LSPhone::make('50000000'))->toBeInstanceOf(LSPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LSPhone::make($number)->isValid())->toBeTrue();
})->with(['26650000000', '26660000000']);

test('is valid with the local key', function () {
    expect(LSPhone::make('50000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LSPhone::make('26650000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LSPhone::make('+26650000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LSPhone::make('0026650000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LSPhone::make('26650000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LSPhone::make('26660000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LSPhone::make('266 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('26650000000');
});

test('is not valid when too short', function () {
    expect(LSPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LSPhone::make('600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LSPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LSPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LSPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LSPhone::make('50000000')->all())->toEqual(['+26650000000', '0026650000000', '26650000000']);
});

test('toArray mirrors all', function () {
    $phone = LSPhone::make('50000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LSPhone::make('26650000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = LSPhone::make('50000000');
    expect($phone->config('key'))->toEqual('266')
        ->and($phone->config('code'))->toEqual('LS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LSPhone::make('266 5-0000000')->number())->toEqual('266 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LSPhone::make('50000000');
    expect($phone->withPlus()->toString())->toEqual('+26650000000')
        ->and($phone->withoutPlus()->toString())->toEqual('26650000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LSPhone::make('50000000')->toString())->toEqual('+26650000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => LSPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => LSPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LSPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => LSPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => LSPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '50000000'], ['phone' => LSPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => LSPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LSPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LSPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LSPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LSPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
