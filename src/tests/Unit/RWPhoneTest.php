<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\RWPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\RWPlaceholder;
use MMAE\Phones\Rules\RWPhoneRule;

test('can create a phone object', function () {
    expect(RWPhone::make('0720000000'))->toBeInstanceOf(RWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(RWPhone::make($number)->isValid())->toBeTrue();
})->with(['250720000000', '250730000000', '250780000000', '250790000000']);

test('is valid with the local key', function () {
    expect(RWPhone::make('0720000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(RWPhone::make('250720000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(RWPhone::make('+250720000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(RWPhone::make('00250720000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(RWPhone::make('250720000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(RWPhone::make('250790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = RWPhone::make('250 7-20000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('250720000000');
});

test('is not valid when too short', function () {
    expect(RWPhone::make('72000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(RWPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(RWPhone::make('999720000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(RWPhone::make('0020000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(RWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(RWPhone::make('0720000000')->all())->toEqual(['+250720000000', '00250720000000', '250720000000', '0720000000']);
});

test('toArray mirrors all', function () {
    $phone = RWPhone::make('0720000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = RWPhone::make('250720000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('720000000');
});

test('config exposes the country schema', function () {
    $phone = RWPhone::make('0720000000');
    expect($phone->config('key'))->toEqual('250')
        ->and($phone->config('code'))->toEqual('RW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(RWPhone::make('250 7-20000000')->number())->toEqual('250 7-20000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = RWPhone::make('0720000000');
    expect($phone->withPlus()->toString())->toEqual('+250720000000')
        ->and($phone->withoutPlus()->toString())->toEqual('250720000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(RWPhone::make('0720000000')->toString())->toEqual('+250720000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0720000000'], ['phone' => RWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '72000000'], ['phone' => RWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(RWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '72000000'], ['phone' => RWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '72000000'], ['phone' => RWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0720000000'], ['phone' => RWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '72000000'], ['phone' => RWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = RWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(RWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('RW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(RWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('RW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(RWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
