<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\JPPhone;
use MMAE\Phones\Placeholders\JPPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\JPPhoneRule;

test('can create a phone object', function () {
    expect(JPPhone::make('07000000000'))->toBeInstanceOf(JPPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(JPPhone::make($number)->isValid())->toBeTrue();
})->with(['817000000000', '818000000000', '819000000000']);

test('is valid with the local key', function () {
    expect(JPPhone::make('07000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(JPPhone::make('817000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(JPPhone::make('+817000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(JPPhone::make('00817000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(JPPhone::make('817000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(JPPhone::make('819000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = JPPhone::make('81 7-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('817000000000');
});

test('is not valid when too short', function () {
    expect(JPPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(JPPhone::make('90000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(JPPhone::make('9997000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(JPPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(JPPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(JPPhone::make('07000000000')->all())->toEqual(['+817000000000', '00817000000000', '817000000000', '07000000000']);
});

test('toArray mirrors all', function () {
    $phone = JPPhone::make('07000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = JPPhone::make('817000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000000');
});

test('config exposes the country schema', function () {
    $phone = JPPhone::make('07000000000');
    expect($phone->config('key'))->toEqual('81')
        ->and($phone->config('code'))->toEqual('JP')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(JPPhone::make('81 7-000000000')->number())->toEqual('81 7-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = JPPhone::make('07000000000');
    expect($phone->withPlus()->toString())->toEqual('+817000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('817000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(JPPhone::make('07000000000')->toString())->toEqual('+817000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '07000000000'], ['phone' => JPPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => JPPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(JPPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000000'], ['phone' => JPPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000000'], ['phone' => JPPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '07000000000'], ['phone' => JPPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => JPPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = JPPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(JPPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('JP');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(JPPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('JP')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(JPPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
