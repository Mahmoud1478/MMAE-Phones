<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TCPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TCPlaceholder;
use MMAE\Phones\Rules\TCPhoneRule;

test('can create a phone object', function () {
    expect(TCPhone::make('16490000000'))->toBeInstanceOf(TCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TCPhone::make($number)->isValid())->toBeTrue();
})->with(['16490000000']);

test('is valid with the local key', function () {
    expect(TCPhone::make('16490000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TCPhone::make('16490000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TCPhone::make('+16490000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TCPhone::make('0016490000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TCPhone::make('16490000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TCPhone::make('16490000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TCPhone::make('1 6-490000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16490000000');
});

test('is not valid when too short', function () {
    expect(TCPhone::make('649000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TCPhone::make('64900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TCPhone::make('9996490000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TCPhone::make('10490000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TCPhone::make('16490000000')->all())->toEqual(['+16490000000', '0016490000000', '16490000000']);
});

test('toArray mirrors all', function () {
    $phone = TCPhone::make('16490000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TCPhone::make('16490000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6490000000');
});

test('config exposes the country schema', function () {
    $phone = TCPhone::make('16490000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('TC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TCPhone::make('1 6-490000000')->number())->toEqual('1 6-490000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TCPhone::make('16490000000');
    expect($phone->withPlus()->toString())->toEqual('+16490000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16490000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TCPhone::make('16490000000')->toString())->toEqual('+16490000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16490000000'], ['phone' => TCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '649000000'], ['phone' => TCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '649000000'], ['phone' => TCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '649000000'], ['phone' => TCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16490000000'], ['phone' => TCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '649000000'], ['phone' => TCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
