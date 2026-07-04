<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TJPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TJPlaceholder;
use MMAE\Phones\Rules\TJPhoneRule;

test('can create a phone object', function () {
    expect(TJPhone::make('400000000'))->toBeInstanceOf(TJPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TJPhone::make($number)->isValid())->toBeTrue();
})->with(['992400000000', '992900000000']);

test('is valid with the local key', function () {
    expect(TJPhone::make('400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TJPhone::make('992400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TJPhone::make('+992400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TJPhone::make('00992400000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TJPhone::make('992400000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TJPhone::make('992900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TJPhone::make('992 4-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('992400000000');
});

test('is not valid when too short', function () {
    expect(TJPhone::make('40000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TJPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TJPhone::make('999400000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TJPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TJPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TJPhone::make('400000000')->all())->toEqual(['+992400000000', '00992400000000', '992400000000']);
});

test('toArray mirrors all', function () {
    $phone = TJPhone::make('400000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TJPhone::make('992400000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('400000000');
});

test('config exposes the country schema', function () {
    $phone = TJPhone::make('400000000');
    expect($phone->config('key'))->toEqual('992')
        ->and($phone->config('code'))->toEqual('TJ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TJPhone::make('992 4-00000000')->number())->toEqual('992 4-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TJPhone::make('400000000');
    expect($phone->withPlus()->toString())->toEqual('+992400000000')
        ->and($phone->withoutPlus()->toString())->toEqual('992400000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TJPhone::make('400000000')->toString())->toEqual('+992400000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '400000000'], ['phone' => TJPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => TJPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TJPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '40000000'], ['phone' => TJPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '40000000'], ['phone' => TJPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '400000000'], ['phone' => TJPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => TJPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TJPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TJPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TJ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TJPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TJ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TJPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
