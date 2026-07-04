<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BAPhone;
use MMAE\Phones\Placeholders\BAPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BAPhoneRule;

test('can create a phone object', function () {
    expect(BAPhone::make('060000000'))->toBeInstanceOf(BAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BAPhone::make($number)->isValid())->toBeTrue();
})->with(['38760000000', '38761000000', '38762000000', '38763000000', '38764000000', '38765000000', '38766000000']);

test('is valid with the local key', function () {
    expect(BAPhone::make('060000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BAPhone::make('38760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BAPhone::make('+38760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BAPhone::make('0038760000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BAPhone::make('38760000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BAPhone::make('38766000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BAPhone::make('387 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('38760000000');
});

test('is not valid when too short', function () {
    expect(BAPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BAPhone::make('660000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BAPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BAPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BAPhone::make('060000000')->all())->toEqual(['+38760000000', '0038760000000', '38760000000', '060000000']);
});

test('toArray mirrors all', function () {
    $phone = BAPhone::make('060000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BAPhone::make('38760000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = BAPhone::make('060000000');
    expect($phone->config('key'))->toEqual('387')
        ->and($phone->config('code'))->toEqual('BA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BAPhone::make('387 6-0000000')->number())->toEqual('387 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BAPhone::make('060000000');
    expect($phone->withPlus()->toString())->toEqual('+38760000000')
        ->and($phone->withoutPlus()->toString())->toEqual('38760000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BAPhone::make('060000000')->toString())->toEqual('+38760000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '060000000'], ['phone' => BAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => BAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '060000000'], ['phone' => BAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
