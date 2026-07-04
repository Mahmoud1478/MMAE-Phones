<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ECPhone;
use MMAE\Phones\Placeholders\ECPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ECPhoneRule;

test('can create a phone object', function () {
    expect(ECPhone::make('0900000000'))->toBeInstanceOf(ECPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ECPhone::make($number)->isValid())->toBeTrue();
})->with(['593900000000']);

test('is valid with the local key', function () {
    expect(ECPhone::make('0900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ECPhone::make('593900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ECPhone::make('+593900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ECPhone::make('00593900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ECPhone::make('593900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ECPhone::make('593900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ECPhone::make('593 9-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('593900000000');
});

test('is not valid when too short', function () {
    expect(ECPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ECPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ECPhone::make('999900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ECPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ECPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ECPhone::make('0900000000')->all())->toEqual(['+593900000000', '00593900000000', '593900000000', '0900000000']);
});

test('toArray mirrors all', function () {
    $phone = ECPhone::make('0900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ECPhone::make('593900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('900000000');
});

test('config exposes the country schema', function () {
    $phone = ECPhone::make('0900000000');
    expect($phone->config('key'))->toEqual('593')
        ->and($phone->config('code'))->toEqual('EC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ECPhone::make('593 9-00000000')->number())->toEqual('593 9-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ECPhone::make('0900000000');
    expect($phone->withPlus()->toString())->toEqual('+593900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('593900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ECPhone::make('0900000000')->toString())->toEqual('+593900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0900000000'], ['phone' => ECPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ECPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ECPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ECPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '90000000'], ['phone' => ECPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0900000000'], ['phone' => ECPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ECPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ECPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ECPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('EC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ECPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('EC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ECPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
