<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GMPhone;
use MMAE\Phones\Placeholders\GMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GMPhoneRule;

test('can create a phone object', function () {
    expect(GMPhone::make('2000000'))->toBeInstanceOf(GMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GMPhone::make($number)->isValid())->toBeTrue();
})->with(['2202000000', '2203000000', '2204000000', '2205000000', '2206000000', '2207000000', '2208000000', '2209000000']);

test('is valid with the local key', function () {
    expect(GMPhone::make('2000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GMPhone::make('2202000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GMPhone::make('+2202000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GMPhone::make('002202000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GMPhone::make('2202000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GMPhone::make('2209000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GMPhone::make('220 2-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2202000000');
});

test('is not valid when too short', function () {
    expect(GMPhone::make('200000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GMPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GMPhone::make('9992000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GMPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GMPhone::make('2000000')->all())->toEqual(['+2202000000', '002202000000', '2202000000']);
});

test('toArray mirrors all', function () {
    $phone = GMPhone::make('2000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GMPhone::make('2202000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2000000');
});

test('config exposes the country schema', function () {
    $phone = GMPhone::make('2000000');
    expect($phone->config('key'))->toEqual('220')
        ->and($phone->config('code'))->toEqual('GM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GMPhone::make('220 2-000000')->number())->toEqual('220 2-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GMPhone::make('2000000');
    expect($phone->withPlus()->toString())->toEqual('+2202000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2202000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GMPhone::make('2000000')->toString())->toEqual('+2202000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => GMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '200000'], ['phone' => GMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '200000'], ['phone' => GMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '200000'], ['phone' => GMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '2000000'], ['phone' => GMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '200000'], ['phone' => GMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
