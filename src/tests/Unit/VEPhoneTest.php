<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VEPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VEPlaceholder;
use MMAE\Phones\Rules\VEPhoneRule;

test('can create a phone object', function () {
    expect(VEPhone::make('04000000000'))->toBeInstanceOf(VEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VEPhone::make($number)->isValid())->toBeTrue();
})->with(['584000000000', '584100000000', '584200000000', '584300000000', '584400000000', '584500000000', '584600000000', '584700000000', '584800000000', '584900000000']);

test('is valid with the local key', function () {
    expect(VEPhone::make('04000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VEPhone::make('584000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VEPhone::make('+584000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VEPhone::make('00584000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VEPhone::make('584000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VEPhone::make('584900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VEPhone::make('58 4-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('584000000000');
});

test('is not valid when too short', function () {
    expect(VEPhone::make('400000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VEPhone::make('49000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VEPhone::make('9994000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VEPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VEPhone::make('04000000000')->all())->toEqual(['+584000000000', '00584000000000', '584000000000', '04000000000']);
});

test('toArray mirrors all', function () {
    $phone = VEPhone::make('04000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VEPhone::make('584000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('4000000000');
});

test('config exposes the country schema', function () {
    $phone = VEPhone::make('04000000000');
    expect($phone->config('key'))->toEqual('58')
        ->and($phone->config('code'))->toEqual('VE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VEPhone::make('58 4-000000000')->number())->toEqual('58 4-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VEPhone::make('04000000000');
    expect($phone->withPlus()->toString())->toEqual('+584000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('584000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VEPhone::make('04000000000')->toString())->toEqual('+584000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '04000000000'], ['phone' => VEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '400000000'], ['phone' => VEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '400000000'], ['phone' => VEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '400000000'], ['phone' => VEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '04000000000'], ['phone' => VEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '400000000'], ['phone' => VEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
