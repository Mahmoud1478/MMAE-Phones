<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SAPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SAPlaceholder;
use MMAE\Phones\Rules\SAPhoneRule;

test('can create a phone object', function () {
    expect(SAPhone::make('0500000000'))->toBeInstanceOf(SAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SAPhone::make($number)->isValid())->toBeTrue();
})->with(['966500000000']);

test('is valid with the local key', function () {
    expect(SAPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SAPhone::make('966500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SAPhone::make('+966500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SAPhone::make('00966500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SAPhone::make('966500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SAPhone::make('966500000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SAPhone::make('966 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('966500000000');
});

test('is not valid when too short', function () {
    expect(SAPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SAPhone::make('5000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SAPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SAPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SAPhone::make('0500000000')->all())->toEqual(['+966500000000', '00966500000000', '966500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = SAPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SAPhone::make('966500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = SAPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('966')
        ->and($phone->config('code'))->toEqual('SA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SAPhone::make('966 5-00000000')->number())->toEqual('966 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SAPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+966500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('966500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SAPhone::make('0500000000')->toString())->toEqual('+966500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => SAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => SAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => SAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => SAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => SAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => SAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
