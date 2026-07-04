<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\EEPhone;
use MMAE\Phones\Placeholders\EEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\EEPhoneRule;

test('can create a phone object', function () {
    expect(EEPhone::make('50000000'))->toBeInstanceOf(EEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(EEPhone::make($number)->isValid())->toBeTrue();
})->with(['37250000000', '37260000000', '37270000000', '37280000000']);

test('is valid with the local key', function () {
    expect(EEPhone::make('50000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(EEPhone::make('37250000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(EEPhone::make('+37250000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(EEPhone::make('0037250000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(EEPhone::make('37250000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(EEPhone::make('37280000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = EEPhone::make('372 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('37250000000');
});

test('is not valid when too short', function () {
    expect(EEPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(EEPhone::make('800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(EEPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(EEPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(EEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(EEPhone::make('50000000')->all())->toEqual(['+37250000000', '0037250000000', '37250000000']);
});

test('toArray mirrors all', function () {
    $phone = EEPhone::make('50000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = EEPhone::make('37250000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = EEPhone::make('50000000');
    expect($phone->config('key'))->toEqual('372')
        ->and($phone->config('code'))->toEqual('EE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(EEPhone::make('372 5-0000000')->number())->toEqual('372 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = EEPhone::make('50000000');
    expect($phone->withPlus()->toString())->toEqual('+37250000000')
        ->and($phone->withoutPlus()->toString())->toEqual('37250000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(EEPhone::make('50000000')->toString())->toEqual('+37250000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => EEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => EEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(EEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => EEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => EEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '50000000'], ['phone' => EEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => EEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = EEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(EEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('EE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(EEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('EE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(EEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
