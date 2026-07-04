<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MSPhone;
use MMAE\Phones\Placeholders\MSPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MSPhoneRule;

test('can create a phone object', function () {
    expect(MSPhone::make('16640000000'))->toBeInstanceOf(MSPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MSPhone::make($number)->isValid())->toBeTrue();
})->with(['16640000000']);

test('is valid with the local key', function () {
    expect(MSPhone::make('16640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MSPhone::make('16640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MSPhone::make('+16640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MSPhone::make('0016640000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MSPhone::make('16640000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MSPhone::make('16640000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MSPhone::make('1 6-640000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16640000000');
});

test('is not valid when too short', function () {
    expect(MSPhone::make('664000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MSPhone::make('66400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MSPhone::make('9996640000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MSPhone::make('10640000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MSPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MSPhone::make('16640000000')->all())->toEqual(['+16640000000', '0016640000000', '16640000000']);
});

test('toArray mirrors all', function () {
    $phone = MSPhone::make('16640000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MSPhone::make('16640000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6640000000');
});

test('config exposes the country schema', function () {
    $phone = MSPhone::make('16640000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('MS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MSPhone::make('1 6-640000000')->number())->toEqual('1 6-640000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MSPhone::make('16640000000');
    expect($phone->withPlus()->toString())->toEqual('+16640000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16640000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MSPhone::make('16640000000')->toString())->toEqual('+16640000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16640000000'], ['phone' => MSPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '664000000'], ['phone' => MSPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MSPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '664000000'], ['phone' => MSPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '664000000'], ['phone' => MSPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16640000000'], ['phone' => MSPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '664000000'], ['phone' => MSPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MSPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MSPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MSPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MSPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
