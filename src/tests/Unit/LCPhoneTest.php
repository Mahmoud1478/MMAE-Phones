<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LCPhone;
use MMAE\Phones\Placeholders\LCPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LCPhoneRule;

test('can create a phone object', function () {
    expect(LCPhone::make('17580000000'))->toBeInstanceOf(LCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LCPhone::make($number)->isValid())->toBeTrue();
})->with(['17580000000']);

test('is valid with the local key', function () {
    expect(LCPhone::make('17580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LCPhone::make('17580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LCPhone::make('+17580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LCPhone::make('0017580000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LCPhone::make('17580000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LCPhone::make('17580000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LCPhone::make('1 7-580000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('17580000000');
});

test('is not valid when too short', function () {
    expect(LCPhone::make('758000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LCPhone::make('75800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LCPhone::make('9997580000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LCPhone::make('10580000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LCPhone::make('17580000000')->all())->toEqual(['+17580000000', '0017580000000', '17580000000']);
});

test('toArray mirrors all', function () {
    $phone = LCPhone::make('17580000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LCPhone::make('17580000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7580000000');
});

test('config exposes the country schema', function () {
    $phone = LCPhone::make('17580000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('LC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LCPhone::make('1 7-580000000')->number())->toEqual('1 7-580000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LCPhone::make('17580000000');
    expect($phone->withPlus()->toString())->toEqual('+17580000000')
        ->and($phone->withoutPlus()->toString())->toEqual('17580000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LCPhone::make('17580000000')->toString())->toEqual('+17580000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17580000000'], ['phone' => LCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '758000000'], ['phone' => LCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '758000000'], ['phone' => LCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '758000000'], ['phone' => LCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17580000000'], ['phone' => LCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '758000000'], ['phone' => LCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
