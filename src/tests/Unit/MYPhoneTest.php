<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MYPhone;
use MMAE\Phones\Placeholders\MYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MYPhoneRule;

test('can create a phone object', function () {
    expect(MYPhone::make('0100000000'))->toBeInstanceOf(MYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MYPhone::make($number)->isValid())->toBeTrue();
})->with(['60100000000', '60110000000', '60120000000', '60130000000', '60140000000', '60150000000', '60160000000', '60170000000', '60180000000', '60190000000', '601000000000', '601100000000', '601200000000', '601300000000', '601400000000', '601500000000', '601600000000', '601700000000', '601800000000', '601900000000']);

test('is valid with the local key', function () {
    expect(MYPhone::make('0100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MYPhone::make('60100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MYPhone::make('+60100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MYPhone::make('0060100000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MYPhone::make('60100000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MYPhone::make('601900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MYPhone::make('60 1-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('60100000000');
});

test('is not valid when too short', function () {
    expect(MYPhone::make('10000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MYPhone::make('19000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MYPhone::make('999100000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MYPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MYPhone::make('0100000000')->all())->toEqual(['+60100000000', '0060100000000', '60100000000', '0100000000']);
});

test('toArray mirrors all', function () {
    $phone = MYPhone::make('0100000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MYPhone::make('60100000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('100000000');
});

test('config exposes the country schema', function () {
    $phone = MYPhone::make('0100000000');
    expect($phone->config('key'))->toEqual('60')
        ->and($phone->config('code'))->toEqual('MY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MYPhone::make('60 1-00000000')->number())->toEqual('60 1-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MYPhone::make('0100000000');
    expect($phone->withPlus()->toString())->toEqual('+60100000000')
        ->and($phone->withoutPlus()->toString())->toEqual('60100000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MYPhone::make('0100000000')->toString())->toEqual('+60100000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0100000000'], ['phone' => MYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '10000000'], ['phone' => MYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '10000000'], ['phone' => MYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '10000000'], ['phone' => MYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0100000000'], ['phone' => MYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '10000000'], ['phone' => MYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
