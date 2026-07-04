<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CIPhone;
use MMAE\Phones\Placeholders\CIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CIPhoneRule;

test('can create a phone object', function () {
    expect(CIPhone::make('0100000000'))->toBeInstanceOf(CIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CIPhone::make($number)->isValid())->toBeTrue();
})->with(['2250100000000', '2250500000000', '2250700000000']);

test('is valid with the local key', function () {
    expect(CIPhone::make('0100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CIPhone::make('2250100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CIPhone::make('+2250100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CIPhone::make('002250100000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CIPhone::make('2250100000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CIPhone::make('2250700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CIPhone::make('225 0-100000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2250100000000');
});

test('is not valid when too short', function () {
    expect(CIPhone::make('010000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CIPhone::make('07000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CIPhone::make('9990100000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CIPhone::make('1100000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CIPhone::make('0100000000')->all())->toEqual(['+2250100000000', '002250100000000', '2250100000000']);
});

test('toArray mirrors all', function () {
    $phone = CIPhone::make('0100000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CIPhone::make('2250100000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('0100000000');
});

test('config exposes the country schema', function () {
    $phone = CIPhone::make('0100000000');
    expect($phone->config('key'))->toEqual('225')
        ->and($phone->config('code'))->toEqual('CI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CIPhone::make('225 0-100000000')->number())->toEqual('225 0-100000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CIPhone::make('0100000000');
    expect($phone->withPlus()->toString())->toEqual('+2250100000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2250100000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CIPhone::make('0100000000')->toString())->toEqual('+2250100000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0100000000'], ['phone' => CIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '010000000'], ['phone' => CIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '010000000'], ['phone' => CIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '010000000'], ['phone' => CIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0100000000'], ['phone' => CIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '010000000'], ['phone' => CIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
