<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TTPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TTPlaceholder;
use MMAE\Phones\Rules\TTPhoneRule;

test('can create a phone object', function () {
    expect(TTPhone::make('18680000000'))->toBeInstanceOf(TTPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TTPhone::make($number)->isValid())->toBeTrue();
})->with(['18680000000']);

test('is valid with the local key', function () {
    expect(TTPhone::make('18680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TTPhone::make('18680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TTPhone::make('+18680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TTPhone::make('0018680000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TTPhone::make('18680000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TTPhone::make('18680000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TTPhone::make('1 8-680000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('18680000000');
});

test('is not valid when too short', function () {
    expect(TTPhone::make('868000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TTPhone::make('86800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TTPhone::make('9998680000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TTPhone::make('10680000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TTPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TTPhone::make('18680000000')->all())->toEqual(['+18680000000', '0018680000000', '18680000000']);
});

test('toArray mirrors all', function () {
    $phone = TTPhone::make('18680000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TTPhone::make('18680000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('8680000000');
});

test('config exposes the country schema', function () {
    $phone = TTPhone::make('18680000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('TT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TTPhone::make('1 8-680000000')->number())->toEqual('1 8-680000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TTPhone::make('18680000000');
    expect($phone->withPlus()->toString())->toEqual('+18680000000')
        ->and($phone->withoutPlus()->toString())->toEqual('18680000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TTPhone::make('18680000000')->toString())->toEqual('+18680000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '18680000000'], ['phone' => TTPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '868000000'], ['phone' => TTPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TTPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '868000000'], ['phone' => TTPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '868000000'], ['phone' => TTPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '18680000000'], ['phone' => TTPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '868000000'], ['phone' => TTPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TTPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TTPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TTPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TTPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
