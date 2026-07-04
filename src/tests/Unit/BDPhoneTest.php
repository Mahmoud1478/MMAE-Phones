<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BDPhone;
use MMAE\Phones\Placeholders\BDPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BDPhoneRule;

test('can create a phone object', function () {
    expect(BDPhone::make('01300000000'))->toBeInstanceOf(BDPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BDPhone::make($number)->isValid())->toBeTrue();
})->with(['8801300000000', '8801400000000', '8801500000000', '8801600000000', '8801700000000', '8801800000000', '8801900000000']);

test('is valid with the local key', function () {
    expect(BDPhone::make('01300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BDPhone::make('8801300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BDPhone::make('+8801300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BDPhone::make('008801300000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BDPhone::make('8801300000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BDPhone::make('8801900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BDPhone::make('880 1-300000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('8801300000000');
});

test('is not valid when too short', function () {
    expect(BDPhone::make('130000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BDPhone::make('19000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BDPhone::make('9991300000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BDPhone::make('00300000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BDPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BDPhone::make('01300000000')->all())->toEqual(['+8801300000000', '008801300000000', '8801300000000', '01300000000']);
});

test('toArray mirrors all', function () {
    $phone = BDPhone::make('01300000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BDPhone::make('8801300000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('1300000000');
});

test('config exposes the country schema', function () {
    $phone = BDPhone::make('01300000000');
    expect($phone->config('key'))->toEqual('880')
        ->and($phone->config('code'))->toEqual('BD')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BDPhone::make('880 1-300000000')->number())->toEqual('880 1-300000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BDPhone::make('01300000000');
    expect($phone->withPlus()->toString())->toEqual('+8801300000000')
        ->and($phone->withoutPlus()->toString())->toEqual('8801300000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BDPhone::make('01300000000')->toString())->toEqual('+8801300000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01300000000'], ['phone' => BDPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '130000000'], ['phone' => BDPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BDPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '130000000'], ['phone' => BDPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '130000000'], ['phone' => BDPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '01300000000'], ['phone' => BDPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '130000000'], ['phone' => BDPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BDPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BDPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BD');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BDPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BD')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BDPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
