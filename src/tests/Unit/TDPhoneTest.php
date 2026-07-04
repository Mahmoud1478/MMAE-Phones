<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TDPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TDPlaceholder;
use MMAE\Phones\Rules\TDPhoneRule;

test('can create a phone object', function () {
    expect(TDPhone::make('60000000'))->toBeInstanceOf(TDPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TDPhone::make($number)->isValid())->toBeTrue();
})->with(['23560000000', '23590000000']);

test('is valid with the local key', function () {
    expect(TDPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TDPhone::make('23560000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TDPhone::make('+23560000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TDPhone::make('0023560000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TDPhone::make('23560000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TDPhone::make('23590000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TDPhone::make('235 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('23560000000');
});

test('is not valid when too short', function () {
    expect(TDPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TDPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TDPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TDPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TDPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TDPhone::make('60000000')->all())->toEqual(['+23560000000', '0023560000000', '23560000000']);
});

test('toArray mirrors all', function () {
    $phone = TDPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TDPhone::make('23560000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = TDPhone::make('60000000');
    expect($phone->config('key'))->toEqual('235')
        ->and($phone->config('code'))->toEqual('TD')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TDPhone::make('235 6-0000000')->number())->toEqual('235 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TDPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+23560000000')
        ->and($phone->withoutPlus()->toString())->toEqual('23560000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TDPhone::make('60000000')->toString())->toEqual('+23560000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => TDPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => TDPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TDPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => TDPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => TDPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => TDPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => TDPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TDPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TDPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TD');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TDPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TD')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TDPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
