<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TWPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TWPlaceholder;
use MMAE\Phones\Rules\TWPhoneRule;

test('can create a phone object', function () {
    expect(TWPhone::make('0900000000'))->toBeInstanceOf(TWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TWPhone::make($number)->isValid())->toBeTrue();
})->with(['886900000000']);

test('is valid with the local key', function () {
    expect(TWPhone::make('0900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TWPhone::make('886900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TWPhone::make('+886900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TWPhone::make('00886900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TWPhone::make('886900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TWPhone::make('886900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TWPhone::make('886 9-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('886900000000');
});

test('is not valid when too short', function () {
    expect(TWPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TWPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TWPhone::make('999900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TWPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TWPhone::make('0900000000')->all())->toEqual(['+886900000000', '00886900000000', '886900000000', '0900000000']);
});

test('toArray mirrors all', function () {
    $phone = TWPhone::make('0900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TWPhone::make('886900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('900000000');
});

test('config exposes the country schema', function () {
    $phone = TWPhone::make('0900000000');
    expect($phone->config('key'))->toEqual('886')
        ->and($phone->config('code'))->toEqual('TW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TWPhone::make('886 9-00000000')->number())->toEqual('886 9-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TWPhone::make('0900000000');
    expect($phone->withPlus()->toString())->toEqual('+886900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('886900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TWPhone::make('0900000000')->toString())->toEqual('+886900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0900000000'], ['phone' => TWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => TWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '90000000'], ['phone' => TWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '90000000'], ['phone' => TWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0900000000'], ['phone' => TWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => TWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
