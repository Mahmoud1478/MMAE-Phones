<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\HRPhone;
use MMAE\Phones\Placeholders\HRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\HRPhoneRule;

test('can create a phone object', function () {
    expect(HRPhone::make('0910000000'))->toBeInstanceOf(HRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(HRPhone::make($number)->isValid())->toBeTrue();
})->with(['385910000000', '385920000000', '385930000000', '385940000000', '385950000000', '385960000000', '385970000000', '385980000000', '385990000000']);

test('is valid with the local key', function () {
    expect(HRPhone::make('0910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(HRPhone::make('385910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(HRPhone::make('+385910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(HRPhone::make('00385910000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(HRPhone::make('385910000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(HRPhone::make('385990000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = HRPhone::make('385 9-10000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('385910000000');
});

test('is not valid when too short', function () {
    expect(HRPhone::make('91000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(HRPhone::make('9900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(HRPhone::make('999910000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(HRPhone::make('0010000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(HRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(HRPhone::make('0910000000')->all())->toEqual(['+385910000000', '00385910000000', '385910000000', '0910000000']);
});

test('toArray mirrors all', function () {
    $phone = HRPhone::make('0910000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = HRPhone::make('385910000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('910000000');
});

test('config exposes the country schema', function () {
    $phone = HRPhone::make('0910000000');
    expect($phone->config('key'))->toEqual('385')
        ->and($phone->config('code'))->toEqual('HR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(HRPhone::make('385 9-10000000')->number())->toEqual('385 9-10000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = HRPhone::make('0910000000');
    expect($phone->withPlus()->toString())->toEqual('+385910000000')
        ->and($phone->withoutPlus()->toString())->toEqual('385910000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(HRPhone::make('0910000000')->toString())->toEqual('+385910000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0910000000'], ['phone' => HRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '91000000'], ['phone' => HRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(HRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '91000000'], ['phone' => HRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '91000000'], ['phone' => HRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0910000000'], ['phone' => HRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '91000000'], ['phone' => HRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = HRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(HRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('HR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(HRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('HR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(HRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
