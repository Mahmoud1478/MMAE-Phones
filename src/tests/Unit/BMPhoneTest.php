<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BMPhone;
use MMAE\Phones\Placeholders\BMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BMPhoneRule;

test('can create a phone object', function () {
    expect(BMPhone::make('14410000000'))->toBeInstanceOf(BMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BMPhone::make($number)->isValid())->toBeTrue();
})->with(['14410000000']);

test('is valid with the local key', function () {
    expect(BMPhone::make('14410000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BMPhone::make('14410000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BMPhone::make('+14410000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BMPhone::make('0014410000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BMPhone::make('14410000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BMPhone::make('14410000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BMPhone::make('1 4-410000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('14410000000');
});

test('is not valid when too short', function () {
    expect(BMPhone::make('441000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BMPhone::make('44100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BMPhone::make('9994410000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BMPhone::make('10410000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BMPhone::make('14410000000')->all())->toEqual(['+14410000000', '0014410000000', '14410000000']);
});

test('toArray mirrors all', function () {
    $phone = BMPhone::make('14410000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BMPhone::make('14410000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('4410000000');
});

test('config exposes the country schema', function () {
    $phone = BMPhone::make('14410000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('BM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BMPhone::make('1 4-410000000')->number())->toEqual('1 4-410000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BMPhone::make('14410000000');
    expect($phone->withPlus()->toString())->toEqual('+14410000000')
        ->and($phone->withoutPlus()->toString())->toEqual('14410000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BMPhone::make('14410000000')->toString())->toEqual('+14410000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '14410000000'], ['phone' => BMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '441000000'], ['phone' => BMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '441000000'], ['phone' => BMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '441000000'], ['phone' => BMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '14410000000'], ['phone' => BMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '441000000'], ['phone' => BMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
