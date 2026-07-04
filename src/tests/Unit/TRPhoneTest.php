<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TRPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TRPlaceholder;
use MMAE\Phones\Rules\TRPhoneRule;

test('can create a phone object', function () {
    expect(TRPhone::make('05000000000'))->toBeInstanceOf(TRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TRPhone::make($number)->isValid())->toBeTrue();
})->with(['905000000000']);

test('is valid with the local key', function () {
    expect(TRPhone::make('05000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TRPhone::make('905000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TRPhone::make('+905000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TRPhone::make('00905000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TRPhone::make('905000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TRPhone::make('905000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TRPhone::make('90 5-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('905000000000');
});

test('is not valid when too short', function () {
    expect(TRPhone::make('500000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TRPhone::make('50000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TRPhone::make('9995000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TRPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TRPhone::make('05000000000')->all())->toEqual(['+905000000000', '00905000000000', '905000000000', '05000000000']);
});

test('toArray mirrors all', function () {
    $phone = TRPhone::make('05000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TRPhone::make('905000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('5000000000');
});

test('config exposes the country schema', function () {
    $phone = TRPhone::make('05000000000');
    expect($phone->config('key'))->toEqual('90')
        ->and($phone->config('code'))->toEqual('TR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TRPhone::make('90 5-000000000')->number())->toEqual('90 5-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TRPhone::make('05000000000');
    expect($phone->withPlus()->toString())->toEqual('+905000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('905000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TRPhone::make('05000000000')->toString())->toEqual('+905000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '05000000000'], ['phone' => TRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '500000000'], ['phone' => TRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '500000000'], ['phone' => TRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '500000000'], ['phone' => TRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '05000000000'], ['phone' => TRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '500000000'], ['phone' => TRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
