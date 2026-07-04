<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VCPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VCPlaceholder;
use MMAE\Phones\Rules\VCPhoneRule;

test('can create a phone object', function () {
    expect(VCPhone::make('17840000000'))->toBeInstanceOf(VCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VCPhone::make($number)->isValid())->toBeTrue();
})->with(['17840000000']);

test('is valid with the local key', function () {
    expect(VCPhone::make('17840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VCPhone::make('17840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VCPhone::make('+17840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VCPhone::make('0017840000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VCPhone::make('17840000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VCPhone::make('17840000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VCPhone::make('1 7-840000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('17840000000');
});

test('is not valid when too short', function () {
    expect(VCPhone::make('784000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VCPhone::make('78400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VCPhone::make('9997840000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VCPhone::make('10840000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VCPhone::make('17840000000')->all())->toEqual(['+17840000000', '0017840000000', '17840000000']);
});

test('toArray mirrors all', function () {
    $phone = VCPhone::make('17840000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VCPhone::make('17840000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7840000000');
});

test('config exposes the country schema', function () {
    $phone = VCPhone::make('17840000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('VC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VCPhone::make('1 7-840000000')->number())->toEqual('1 7-840000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VCPhone::make('17840000000');
    expect($phone->withPlus()->toString())->toEqual('+17840000000')
        ->and($phone->withoutPlus()->toString())->toEqual('17840000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VCPhone::make('17840000000')->toString())->toEqual('+17840000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17840000000'], ['phone' => VCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '784000000'], ['phone' => VCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '784000000'], ['phone' => VCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '784000000'], ['phone' => VCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17840000000'], ['phone' => VCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '784000000'], ['phone' => VCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
