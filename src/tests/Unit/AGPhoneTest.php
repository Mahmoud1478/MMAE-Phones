<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AGPhone;
use MMAE\Phones\Placeholders\AGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AGPhoneRule;

test('can create a phone object', function () {
    expect(AGPhone::make('12680000000'))->toBeInstanceOf(AGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AGPhone::make($number)->isValid())->toBeTrue();
})->with(['12680000000']);

test('is valid with the local key', function () {
    expect(AGPhone::make('12680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AGPhone::make('12680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AGPhone::make('+12680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AGPhone::make('0012680000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AGPhone::make('12680000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AGPhone::make('12680000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AGPhone::make('1 2-680000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('12680000000');
});

test('is not valid when too short', function () {
    expect(AGPhone::make('268000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AGPhone::make('26800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AGPhone::make('9992680000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AGPhone::make('10680000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AGPhone::make('12680000000')->all())->toEqual(['+12680000000', '0012680000000', '12680000000']);
});

test('toArray mirrors all', function () {
    $phone = AGPhone::make('12680000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AGPhone::make('12680000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2680000000');
});

test('config exposes the country schema', function () {
    $phone = AGPhone::make('12680000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('AG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AGPhone::make('1 2-680000000')->number())->toEqual('1 2-680000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AGPhone::make('12680000000');
    expect($phone->withPlus()->toString())->toEqual('+12680000000')
        ->and($phone->withoutPlus()->toString())->toEqual('12680000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AGPhone::make('12680000000')->toString())->toEqual('+12680000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '12680000000'], ['phone' => AGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '268000000'], ['phone' => AGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '268000000'], ['phone' => AGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '268000000'], ['phone' => AGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '12680000000'], ['phone' => AGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '268000000'], ['phone' => AGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
