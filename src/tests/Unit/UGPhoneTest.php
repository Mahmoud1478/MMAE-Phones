<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\UGPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\UGPlaceholder;
use MMAE\Phones\Rules\UGPhoneRule;

test('can create a phone object', function () {
    expect(UGPhone::make('0700000000'))->toBeInstanceOf(UGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(UGPhone::make($number)->isValid())->toBeTrue();
})->with(['256700000000', '256710000000', '256720000000', '256730000000', '256740000000', '256750000000', '256760000000', '256770000000', '256780000000', '256790000000']);

test('is valid with the local key', function () {
    expect(UGPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(UGPhone::make('256700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(UGPhone::make('+256700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(UGPhone::make('00256700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(UGPhone::make('256700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(UGPhone::make('256790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = UGPhone::make('256 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('256700000000');
});

test('is not valid when too short', function () {
    expect(UGPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(UGPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(UGPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(UGPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(UGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(UGPhone::make('0700000000')->all())->toEqual(['+256700000000', '00256700000000', '256700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = UGPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = UGPhone::make('256700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = UGPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('256')
        ->and($phone->config('code'))->toEqual('UG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(UGPhone::make('256 7-00000000')->number())->toEqual('256 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = UGPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+256700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('256700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(UGPhone::make('0700000000')->toString())->toEqual('+256700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => UGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => UGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(UGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => UGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => UGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => UGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => UGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = UGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(UGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('UG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(UGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('UG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(UGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
