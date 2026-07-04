<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\INPhone;
use MMAE\Phones\Placeholders\INPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\INPhoneRule;

test('can create a phone object', function () {
    expect(INPhone::make('06000000000'))->toBeInstanceOf(INPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(INPhone::make($number)->isValid())->toBeTrue();
})->with(['916000000000', '917000000000', '918000000000', '919000000000']);

test('is valid with the local key', function () {
    expect(INPhone::make('06000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(INPhone::make('916000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(INPhone::make('+916000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(INPhone::make('00916000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(INPhone::make('916000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(INPhone::make('919000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = INPhone::make('91 6-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('916000000000');
});

test('is not valid when too short', function () {
    expect(INPhone::make('600000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(INPhone::make('90000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(INPhone::make('9996000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(INPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(INPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(INPhone::make('06000000000')->all())->toEqual(['+916000000000', '00916000000000', '916000000000', '06000000000']);
});

test('toArray mirrors all', function () {
    $phone = INPhone::make('06000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = INPhone::make('916000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6000000000');
});

test('config exposes the country schema', function () {
    $phone = INPhone::make('06000000000');
    expect($phone->config('key'))->toEqual('91')
        ->and($phone->config('code'))->toEqual('IN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(INPhone::make('91 6-000000000')->number())->toEqual('91 6-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = INPhone::make('06000000000');
    expect($phone->withPlus()->toString())->toEqual('+916000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('916000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(INPhone::make('06000000000')->toString())->toEqual('+916000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '06000000000'], ['phone' => INPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '600000000'], ['phone' => INPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(INPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '600000000'], ['phone' => INPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '600000000'], ['phone' => INPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '06000000000'], ['phone' => INPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '600000000'], ['phone' => INPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = INPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(INPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(INPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(INPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
