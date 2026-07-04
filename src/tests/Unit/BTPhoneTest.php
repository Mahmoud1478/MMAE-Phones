<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BTPhone;
use MMAE\Phones\Placeholders\BTPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BTPhoneRule;

test('can create a phone object', function () {
    expect(BTPhone::make('17000000'))->toBeInstanceOf(BTPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BTPhone::make($number)->isValid())->toBeTrue();
})->with(['97517000000']);

test('is valid with the local key', function () {
    expect(BTPhone::make('17000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BTPhone::make('97517000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BTPhone::make('+97517000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BTPhone::make('0097517000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BTPhone::make('97517000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BTPhone::make('97517000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BTPhone::make('975 1-7000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('97517000000');
});

test('is not valid when too short', function () {
    expect(BTPhone::make('1700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BTPhone::make('170000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BTPhone::make('99917000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BTPhone::make('07000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BTPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BTPhone::make('17000000')->all())->toEqual(['+97517000000', '0097517000000', '97517000000']);
});

test('toArray mirrors all', function () {
    $phone = BTPhone::make('17000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BTPhone::make('97517000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('17000000');
});

test('config exposes the country schema', function () {
    $phone = BTPhone::make('17000000');
    expect($phone->config('key'))->toEqual('975')
        ->and($phone->config('code'))->toEqual('BT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BTPhone::make('975 1-7000000')->number())->toEqual('975 1-7000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BTPhone::make('17000000');
    expect($phone->withPlus()->toString())->toEqual('+97517000000')
        ->and($phone->withoutPlus()->toString())->toEqual('97517000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BTPhone::make('17000000')->toString())->toEqual('+97517000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17000000'], ['phone' => BTPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '1700000'], ['phone' => BTPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BTPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '1700000'], ['phone' => BTPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '1700000'], ['phone' => BTPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17000000'], ['phone' => BTPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '1700000'], ['phone' => BTPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BTPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BTPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BTPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BTPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
