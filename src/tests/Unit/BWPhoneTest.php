<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BWPhone;
use MMAE\Phones\Placeholders\BWPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BWPhoneRule;

test('can create a phone object', function () {
    expect(BWPhone::make('71000000'))->toBeInstanceOf(BWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BWPhone::make($number)->isValid())->toBeTrue();
})->with(['26771000000', '26772000000', '26773000000', '26774000000', '26775000000', '26776000000', '26777000000', '26778000000']);

test('is valid with the local key', function () {
    expect(BWPhone::make('71000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BWPhone::make('26771000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BWPhone::make('+26771000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BWPhone::make('0026771000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BWPhone::make('26771000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BWPhone::make('26778000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BWPhone::make('267 7-1000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('26771000000');
});

test('is not valid when too short', function () {
    expect(BWPhone::make('7100000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BWPhone::make('780000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BWPhone::make('99971000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BWPhone::make('01000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BWPhone::make('71000000')->all())->toEqual(['+26771000000', '0026771000000', '26771000000']);
});

test('toArray mirrors all', function () {
    $phone = BWPhone::make('71000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BWPhone::make('26771000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('71000000');
});

test('config exposes the country schema', function () {
    $phone = BWPhone::make('71000000');
    expect($phone->config('key'))->toEqual('267')
        ->and($phone->config('code'))->toEqual('BW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BWPhone::make('267 7-1000000')->number())->toEqual('267 7-1000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BWPhone::make('71000000');
    expect($phone->withPlus()->toString())->toEqual('+26771000000')
        ->and($phone->withoutPlus()->toString())->toEqual('26771000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BWPhone::make('71000000')->toString())->toEqual('+26771000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '71000000'], ['phone' => BWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7100000'], ['phone' => BWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7100000'], ['phone' => BWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7100000'], ['phone' => BWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '71000000'], ['phone' => BWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7100000'], ['phone' => BWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
