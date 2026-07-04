<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ISPhone;
use MMAE\Phones\Placeholders\ISPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ISPhoneRule;

test('can create a phone object', function () {
    expect(ISPhone::make('3000000'))->toBeInstanceOf(ISPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ISPhone::make($number)->isValid())->toBeTrue();
})->with(['3543000000', '3546000000', '3547000000', '3548000000']);

test('is valid with the local key', function () {
    expect(ISPhone::make('3000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ISPhone::make('3543000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ISPhone::make('+3543000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ISPhone::make('003543000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ISPhone::make('3543000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ISPhone::make('3548000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ISPhone::make('354 3-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('3543000000');
});

test('is not valid when too short', function () {
    expect(ISPhone::make('300000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ISPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ISPhone::make('9993000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ISPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ISPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ISPhone::make('3000000')->all())->toEqual(['+3543000000', '003543000000', '3543000000']);
});

test('toArray mirrors all', function () {
    $phone = ISPhone::make('3000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ISPhone::make('3543000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3000000');
});

test('config exposes the country schema', function () {
    $phone = ISPhone::make('3000000');
    expect($phone->config('key'))->toEqual('354')
        ->and($phone->config('code'))->toEqual('IS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ISPhone::make('354 3-000000')->number())->toEqual('354 3-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ISPhone::make('3000000');
    expect($phone->withPlus()->toString())->toEqual('+3543000000')
        ->and($phone->withoutPlus()->toString())->toEqual('3543000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ISPhone::make('3000000')->toString())->toEqual('+3543000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => ISPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '300000'], ['phone' => ISPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ISPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '300000'], ['phone' => ISPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '300000'], ['phone' => ISPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '3000000'], ['phone' => ISPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '300000'], ['phone' => ISPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ISPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ISPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ISPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ISPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
