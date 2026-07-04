<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SBPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SBPlaceholder;
use MMAE\Phones\Rules\SBPhoneRule;

test('can create a phone object', function () {
    expect(SBPhone::make('7000000'))->toBeInstanceOf(SBPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SBPhone::make($number)->isValid())->toBeTrue();
})->with(['6777000000', '6778000000']);

test('is valid with the local key', function () {
    expect(SBPhone::make('7000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SBPhone::make('6777000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SBPhone::make('+6777000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SBPhone::make('006777000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SBPhone::make('6777000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SBPhone::make('6778000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SBPhone::make('677 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6777000000');
});

test('is not valid when too short', function () {
    expect(SBPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SBPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SBPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SBPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SBPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SBPhone::make('7000000')->all())->toEqual(['+6777000000', '006777000000', '6777000000']);
});

test('toArray mirrors all', function () {
    $phone = SBPhone::make('7000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SBPhone::make('6777000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = SBPhone::make('7000000');
    expect($phone->config('key'))->toEqual('677')
        ->and($phone->config('code'))->toEqual('SB')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SBPhone::make('677 7-000000')->number())->toEqual('677 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SBPhone::make('7000000');
    expect($phone->withPlus()->toString())->toEqual('+6777000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6777000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SBPhone::make('7000000')->toString())->toEqual('+6777000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => SBPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => SBPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SBPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => SBPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => SBPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7000000'], ['phone' => SBPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => SBPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SBPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SBPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SB');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SBPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SB')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SBPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
