<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AFPhone;
use MMAE\Phones\Placeholders\AFPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AFPhoneRule;

test('can create a phone object', function () {
    expect(AFPhone::make('0700000000'))->toBeInstanceOf(AFPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AFPhone::make($number)->isValid())->toBeTrue();
})->with(['93700000000', '93710000000', '93720000000', '93730000000', '93740000000', '93750000000', '93760000000', '93770000000', '93780000000', '93790000000']);

test('is valid with the local key', function () {
    expect(AFPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AFPhone::make('93700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AFPhone::make('+93700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AFPhone::make('0093700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AFPhone::make('93700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AFPhone::make('93790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AFPhone::make('93 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('93700000000');
});

test('is not valid when too short', function () {
    expect(AFPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AFPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AFPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AFPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AFPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AFPhone::make('0700000000')->all())->toEqual(['+93700000000', '0093700000000', '93700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = AFPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AFPhone::make('93700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = AFPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('93')
        ->and($phone->config('code'))->toEqual('AF')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AFPhone::make('93 7-00000000')->number())->toEqual('93 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AFPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+93700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('93700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AFPhone::make('0700000000')->toString())->toEqual('+93700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => AFPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => AFPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AFPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => AFPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => AFPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => AFPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => AFPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AFPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AFPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AF');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AFPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AF')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AFPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
