<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ARPhone;
use MMAE\Phones\Placeholders\ARPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ARPhoneRule;

test('can create a phone object', function () {
    expect(ARPhone::make('0900000000'))->toBeInstanceOf(ARPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ARPhone::make($number)->isValid())->toBeTrue();
})->with(['54900000000', '5490000000000']);

test('is valid with the local key', function () {
    expect(ARPhone::make('0900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ARPhone::make('54900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ARPhone::make('+54900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ARPhone::make('0054900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ARPhone::make('54900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ARPhone::make('5490000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ARPhone::make('54 9-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('54900000000');
});

test('is not valid when too short', function () {
    expect(ARPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ARPhone::make('900000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ARPhone::make('999900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ARPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ARPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ARPhone::make('0900000000')->all())->toEqual(['+54900000000', '0054900000000', '54900000000', '0900000000']);
});

test('toArray mirrors all', function () {
    $phone = ARPhone::make('0900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ARPhone::make('54900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('900000000');
});

test('config exposes the country schema', function () {
    $phone = ARPhone::make('0900000000');
    expect($phone->config('key'))->toEqual('54')
        ->and($phone->config('code'))->toEqual('AR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ARPhone::make('54 9-00000000')->number())->toEqual('54 9-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ARPhone::make('0900000000');
    expect($phone->withPlus()->toString())->toEqual('+54900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('54900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ARPhone::make('0900000000')->toString())->toEqual('+54900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0900000000'], ['phone' => ARPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ARPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ARPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ARPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '90000000'], ['phone' => ARPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0900000000'], ['phone' => ARPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => ARPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ARPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ARPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ARPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ARPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
