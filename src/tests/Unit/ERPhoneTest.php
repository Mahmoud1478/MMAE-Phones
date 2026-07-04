<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ERPhone;
use MMAE\Phones\Placeholders\ERPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ERPhoneRule;

test('can create a phone object', function () {
    expect(ERPhone::make('07000000'))->toBeInstanceOf(ERPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ERPhone::make($number)->isValid())->toBeTrue();
})->with(['2917000000']);

test('is valid with the local key', function () {
    expect(ERPhone::make('07000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ERPhone::make('2917000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ERPhone::make('+2917000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ERPhone::make('002917000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ERPhone::make('2917000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ERPhone::make('2917000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ERPhone::make('291 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2917000000');
});

test('is not valid when too short', function () {
    expect(ERPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ERPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ERPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ERPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ERPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ERPhone::make('07000000')->all())->toEqual(['+2917000000', '002917000000', '2917000000', '07000000']);
});

test('toArray mirrors all', function () {
    $phone = ERPhone::make('07000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ERPhone::make('2917000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = ERPhone::make('07000000');
    expect($phone->config('key'))->toEqual('291')
        ->and($phone->config('code'))->toEqual('ER')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ERPhone::make('291 7-000000')->number())->toEqual('291 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ERPhone::make('07000000');
    expect($phone->withPlus()->toString())->toEqual('+2917000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2917000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ERPhone::make('07000000')->toString())->toEqual('+2917000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '07000000'], ['phone' => ERPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => ERPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ERPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => ERPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => ERPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '07000000'], ['phone' => ERPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => ERPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ERPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ERPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ER');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ERPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ER')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ERPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
