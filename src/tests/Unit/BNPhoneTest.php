<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BNPhone;
use MMAE\Phones\Placeholders\BNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BNPhoneRule;

test('can create a phone object', function () {
    expect(BNPhone::make('7000000'))->toBeInstanceOf(BNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BNPhone::make($number)->isValid())->toBeTrue();
})->with(['6737000000', '6738000000']);

test('is valid with the local key', function () {
    expect(BNPhone::make('7000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BNPhone::make('6737000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BNPhone::make('+6737000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BNPhone::make('006737000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BNPhone::make('6737000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BNPhone::make('6738000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BNPhone::make('673 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6737000000');
});

test('is not valid when too short', function () {
    expect(BNPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BNPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BNPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BNPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BNPhone::make('7000000')->all())->toEqual(['+6737000000', '006737000000', '6737000000']);
});

test('toArray mirrors all', function () {
    $phone = BNPhone::make('7000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BNPhone::make('6737000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = BNPhone::make('7000000');
    expect($phone->config('key'))->toEqual('673')
        ->and($phone->config('code'))->toEqual('BN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BNPhone::make('673 7-000000')->number())->toEqual('673 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BNPhone::make('7000000');
    expect($phone->withPlus()->toString())->toEqual('+6737000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6737000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BNPhone::make('7000000')->toString())->toEqual('+6737000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => BNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => BNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => BNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => BNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7000000'], ['phone' => BNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => BNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
