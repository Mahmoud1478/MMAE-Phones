<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BEPhone;
use MMAE\Phones\Placeholders\BEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BEPhoneRule;

test('can create a phone object', function () {
    expect(BEPhone::make('0450000000'))->toBeInstanceOf(BEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BEPhone::make($number)->isValid())->toBeTrue();
})->with(['32450000000', '32460000000', '32470000000', '32480000000', '32490000000']);

test('is valid with the local key', function () {
    expect(BEPhone::make('0450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BEPhone::make('32450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BEPhone::make('+32450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BEPhone::make('0032450000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BEPhone::make('32450000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BEPhone::make('32490000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BEPhone::make('32 4-50000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('32450000000');
});

test('is not valid when too short', function () {
    expect(BEPhone::make('45000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BEPhone::make('4900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BEPhone::make('999450000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BEPhone::make('0050000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BEPhone::make('0450000000')->all())->toEqual(['+32450000000', '0032450000000', '32450000000', '0450000000']);
});

test('toArray mirrors all', function () {
    $phone = BEPhone::make('0450000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BEPhone::make('32450000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('450000000');
});

test('config exposes the country schema', function () {
    $phone = BEPhone::make('0450000000');
    expect($phone->config('key'))->toEqual('32')
        ->and($phone->config('code'))->toEqual('BE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BEPhone::make('32 4-50000000')->number())->toEqual('32 4-50000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BEPhone::make('0450000000');
    expect($phone->withPlus()->toString())->toEqual('+32450000000')
        ->and($phone->withoutPlus()->toString())->toEqual('32450000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BEPhone::make('0450000000')->toString())->toEqual('+32450000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0450000000'], ['phone' => BEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '45000000'], ['phone' => BEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '45000000'], ['phone' => BEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '45000000'], ['phone' => BEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0450000000'], ['phone' => BEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '45000000'], ['phone' => BEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
