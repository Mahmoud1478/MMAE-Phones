<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\IDPhone;
use MMAE\Phones\Placeholders\IDPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\IDPhoneRule;

test('can create a phone object', function () {
    expect(IDPhone::make('0810000000'))->toBeInstanceOf(IDPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(IDPhone::make($number)->isValid())->toBeTrue();
})->with(['62810000000', '62820000000', '62830000000', '62840000000', '62850000000', '62860000000', '62870000000', '62880000000', '62890000000', '62810000000000', '62820000000000', '62830000000000', '62840000000000', '62850000000000', '62860000000000', '62870000000000', '62880000000000', '62890000000000']);

test('is valid with the local key', function () {
    expect(IDPhone::make('0810000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(IDPhone::make('62810000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(IDPhone::make('+62810000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(IDPhone::make('0062810000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(IDPhone::make('62810000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(IDPhone::make('62890000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = IDPhone::make('62 8-10000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('62810000000');
});

test('is not valid when too short', function () {
    expect(IDPhone::make('81000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(IDPhone::make('8900000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(IDPhone::make('999810000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(IDPhone::make('0010000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(IDPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(IDPhone::make('0810000000')->all())->toEqual(['+62810000000', '0062810000000', '62810000000', '0810000000']);
});

test('toArray mirrors all', function () {
    $phone = IDPhone::make('0810000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = IDPhone::make('62810000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('810000000');
});

test('config exposes the country schema', function () {
    $phone = IDPhone::make('0810000000');
    expect($phone->config('key'))->toEqual('62')
        ->and($phone->config('code'))->toEqual('ID')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(IDPhone::make('62 8-10000000')->number())->toEqual('62 8-10000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = IDPhone::make('0810000000');
    expect($phone->withPlus()->toString())->toEqual('+62810000000')
        ->and($phone->withoutPlus()->toString())->toEqual('62810000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(IDPhone::make('0810000000')->toString())->toEqual('+62810000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0810000000'], ['phone' => IDPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '81000000'], ['phone' => IDPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(IDPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '81000000'], ['phone' => IDPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '81000000'], ['phone' => IDPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0810000000'], ['phone' => IDPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '81000000'], ['phone' => IDPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = IDPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(IDPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ID');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(IDPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ID')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(IDPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
