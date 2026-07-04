<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\YEPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\YEPlaceholder;
use MMAE\Phones\Rules\YEPhoneRule;

test('can create a phone object', function () {
    expect(YEPhone::make('0700000000'))->toBeInstanceOf(YEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(YEPhone::make($number)->isValid())->toBeTrue();
})->with(['967700000000', '967710000000', '967720000000', '967730000000', '967740000000', '967750000000', '967760000000', '967770000000', '967780000000', '967790000000']);

test('is valid with the local key', function () {
    expect(YEPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(YEPhone::make('967700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(YEPhone::make('+967700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(YEPhone::make('00967700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(YEPhone::make('967700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(YEPhone::make('967790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = YEPhone::make('967 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('967700000000');
});

test('is not valid when too short', function () {
    expect(YEPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(YEPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(YEPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(YEPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(YEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(YEPhone::make('0700000000')->all())->toEqual(['+967700000000', '00967700000000', '967700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = YEPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = YEPhone::make('967700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = YEPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('967')
        ->and($phone->config('code'))->toEqual('YE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(YEPhone::make('967 7-00000000')->number())->toEqual('967 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = YEPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+967700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('967700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(YEPhone::make('0700000000')->toString())->toEqual('+967700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => YEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => YEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(YEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => YEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => YEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => YEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => YEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = YEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(YEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('YE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(YEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('YE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(YEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
