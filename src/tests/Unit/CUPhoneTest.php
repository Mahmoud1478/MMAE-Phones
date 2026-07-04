<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CUPhone;
use MMAE\Phones\Placeholders\CUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CUPhoneRule;

test('can create a phone object', function () {
    expect(CUPhone::make('050000000'))->toBeInstanceOf(CUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CUPhone::make($number)->isValid())->toBeTrue();
})->with(['5350000000']);

test('is valid with the local key', function () {
    expect(CUPhone::make('050000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CUPhone::make('5350000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CUPhone::make('+5350000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CUPhone::make('005350000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CUPhone::make('5350000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CUPhone::make('5350000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CUPhone::make('53 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('5350000000');
});

test('is not valid when too short', function () {
    expect(CUPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CUPhone::make('500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CUPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CUPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CUPhone::make('050000000')->all())->toEqual(['+5350000000', '005350000000', '5350000000', '050000000']);
});

test('toArray mirrors all', function () {
    $phone = CUPhone::make('050000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CUPhone::make('5350000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = CUPhone::make('050000000');
    expect($phone->config('key'))->toEqual('53')
        ->and($phone->config('code'))->toEqual('CU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CUPhone::make('53 5-0000000')->number())->toEqual('53 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CUPhone::make('050000000');
    expect($phone->withPlus()->toString())->toEqual('+5350000000')
        ->and($phone->withoutPlus()->toString())->toEqual('5350000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CUPhone::make('050000000')->toString())->toEqual('+5350000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '050000000'], ['phone' => CUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => CUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => CUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => CUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '050000000'], ['phone' => CUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => CUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
