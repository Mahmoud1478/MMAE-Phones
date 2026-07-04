<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PLPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\PLPlaceholder;
use MMAE\Phones\Rules\PLPhoneRule;

test('can create a phone object', function () {
    expect(PLPhone::make('500000000'))->toBeInstanceOf(PLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PLPhone::make($number)->isValid())->toBeTrue();
})->with(['48500000000', '48600000000', '48700000000', '48800000000']);

test('is valid with the local key', function () {
    expect(PLPhone::make('500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PLPhone::make('48500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PLPhone::make('+48500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PLPhone::make('0048500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PLPhone::make('48500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PLPhone::make('48800000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PLPhone::make('48 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('48500000000');
});

test('is not valid when too short', function () {
    expect(PLPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PLPhone::make('8000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PLPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PLPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PLPhone::make('500000000')->all())->toEqual(['+48500000000', '0048500000000', '48500000000']);
});

test('toArray mirrors all', function () {
    $phone = PLPhone::make('500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PLPhone::make('48500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = PLPhone::make('500000000');
    expect($phone->config('key'))->toEqual('48')
        ->and($phone->config('code'))->toEqual('PL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PLPhone::make('48 5-00000000')->number())->toEqual('48 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PLPhone::make('500000000');
    expect($phone->withPlus()->toString())->toEqual('+48500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('48500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PLPhone::make('500000000')->toString())->toEqual('+48500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '500000000'], ['phone' => PLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => PLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => PLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => PLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '500000000'], ['phone' => PLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => PLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
