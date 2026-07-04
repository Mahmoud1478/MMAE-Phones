<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BYPhone;
use MMAE\Phones\Placeholders\BYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BYPhoneRule;

test('can create a phone object', function () {
    expect(BYPhone::make('0200000000'))->toBeInstanceOf(BYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BYPhone::make($number)->isValid())->toBeTrue();
})->with(['375200000000', '375210000000', '375220000000', '375230000000', '375240000000', '375250000000', '375260000000', '375270000000', '375280000000', '375290000000', '375300000000', '375310000000', '375320000000', '375330000000', '375340000000', '375350000000', '375360000000', '375370000000', '375380000000', '375390000000', '375400000000', '375410000000', '375420000000', '375430000000', '375440000000', '375450000000', '375460000000', '375470000000', '375480000000', '375490000000']);

test('is valid with the local key', function () {
    expect(BYPhone::make('0200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BYPhone::make('375200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BYPhone::make('+375200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BYPhone::make('00375200000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BYPhone::make('375200000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BYPhone::make('375490000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BYPhone::make('375 2-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('375200000000');
});

test('is not valid when too short', function () {
    expect(BYPhone::make('20000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BYPhone::make('4900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BYPhone::make('999200000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BYPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BYPhone::make('0200000000')->all())->toEqual(['+375200000000', '00375200000000', '375200000000', '0200000000']);
});

test('toArray mirrors all', function () {
    $phone = BYPhone::make('0200000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BYPhone::make('375200000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000000');
});

test('config exposes the country schema', function () {
    $phone = BYPhone::make('0200000000');
    expect($phone->config('key'))->toEqual('375')
        ->and($phone->config('code'))->toEqual('BY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BYPhone::make('375 2-00000000')->number())->toEqual('375 2-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BYPhone::make('0200000000');
    expect($phone->withPlus()->toString())->toEqual('+375200000000')
        ->and($phone->withoutPlus()->toString())->toEqual('375200000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BYPhone::make('0200000000')->toString())->toEqual('+375200000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0200000000'], ['phone' => BYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => BYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000000'], ['phone' => BYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000000'], ['phone' => BYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0200000000'], ['phone' => BYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => BYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
