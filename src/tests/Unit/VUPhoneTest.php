<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VUPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VUPlaceholder;
use MMAE\Phones\Rules\VUPhoneRule;

test('can create a phone object', function () {
    expect(VUPhone::make('5000000'))->toBeInstanceOf(VUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VUPhone::make($number)->isValid())->toBeTrue();
})->with(['6785000000', '6787000000']);

test('is valid with the local key', function () {
    expect(VUPhone::make('5000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VUPhone::make('6785000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VUPhone::make('+6785000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VUPhone::make('006785000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VUPhone::make('6785000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VUPhone::make('6787000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VUPhone::make('678 5-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6785000000');
});

test('is not valid when too short', function () {
    expect(VUPhone::make('500000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VUPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VUPhone::make('9995000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VUPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VUPhone::make('5000000')->all())->toEqual(['+6785000000', '006785000000', '6785000000']);
});

test('toArray mirrors all', function () {
    $phone = VUPhone::make('5000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VUPhone::make('6785000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('5000000');
});

test('config exposes the country schema', function () {
    $phone = VUPhone::make('5000000');
    expect($phone->config('key'))->toEqual('678')
        ->and($phone->config('code'))->toEqual('VU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VUPhone::make('678 5-000000')->number())->toEqual('678 5-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VUPhone::make('5000000');
    expect($phone->withPlus()->toString())->toEqual('+6785000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6785000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VUPhone::make('5000000')->toString())->toEqual('+6785000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => VUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '500000'], ['phone' => VUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '500000'], ['phone' => VUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '500000'], ['phone' => VUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '5000000'], ['phone' => VUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '500000'], ['phone' => VUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
