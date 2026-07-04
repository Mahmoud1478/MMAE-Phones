<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BGPhone;
use MMAE\Phones\Placeholders\BGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BGPhoneRule;

test('can create a phone object', function () {
    expect(BGPhone::make('04000000'))->toBeInstanceOf(BGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BGPhone::make($number)->isValid())->toBeTrue();
})->with(['3594000000', '3598000000', '3599000000', '359400000000', '359800000000', '359900000000']);

test('is valid with the local key', function () {
    expect(BGPhone::make('04000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BGPhone::make('3594000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BGPhone::make('+3594000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BGPhone::make('003594000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BGPhone::make('3594000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BGPhone::make('359900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BGPhone::make('359 4-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('3594000000');
});

test('is not valid when too short', function () {
    expect(BGPhone::make('400000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BGPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BGPhone::make('9994000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BGPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BGPhone::make('04000000')->all())->toEqual(['+3594000000', '003594000000', '3594000000', '04000000']);
});

test('toArray mirrors all', function () {
    $phone = BGPhone::make('04000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BGPhone::make('3594000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('4000000');
});

test('config exposes the country schema', function () {
    $phone = BGPhone::make('04000000');
    expect($phone->config('key'))->toEqual('359')
        ->and($phone->config('code'))->toEqual('BG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BGPhone::make('359 4-000000')->number())->toEqual('359 4-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BGPhone::make('04000000');
    expect($phone->withPlus()->toString())->toEqual('+3594000000')
        ->and($phone->withoutPlus()->toString())->toEqual('3594000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BGPhone::make('04000000')->toString())->toEqual('+3594000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '04000000'], ['phone' => BGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '400000'], ['phone' => BGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '400000'], ['phone' => BGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '400000'], ['phone' => BGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '04000000'], ['phone' => BGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '400000'], ['phone' => BGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
