<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TOPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TOPlaceholder;
use MMAE\Phones\Rules\TOPhoneRule;

test('can create a phone object', function () {
    expect(TOPhone::make('7000000'))->toBeInstanceOf(TOPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TOPhone::make($number)->isValid())->toBeTrue();
})->with(['6767000000', '6768000000']);

test('is valid with the local key', function () {
    expect(TOPhone::make('7000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TOPhone::make('6767000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TOPhone::make('+6767000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TOPhone::make('006767000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TOPhone::make('6767000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TOPhone::make('6768000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TOPhone::make('676 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6767000000');
});

test('is not valid when too short', function () {
    expect(TOPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TOPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TOPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TOPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TOPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TOPhone::make('7000000')->all())->toEqual(['+6767000000', '006767000000', '6767000000']);
});

test('toArray mirrors all', function () {
    $phone = TOPhone::make('7000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TOPhone::make('6767000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = TOPhone::make('7000000');
    expect($phone->config('key'))->toEqual('676')
        ->and($phone->config('code'))->toEqual('TO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TOPhone::make('676 7-000000')->number())->toEqual('676 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TOPhone::make('7000000');
    expect($phone->withPlus()->toString())->toEqual('+6767000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6767000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TOPhone::make('7000000')->toString())->toEqual('+6767000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => TOPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => TOPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TOPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => TOPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => TOPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7000000'], ['phone' => TOPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => TOPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TOPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TOPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TOPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TOPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
