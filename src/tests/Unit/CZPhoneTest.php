<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CZPhone;
use MMAE\Phones\Placeholders\CZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CZPhoneRule;

test('can create a phone object', function () {
    expect(CZPhone::make('600000000'))->toBeInstanceOf(CZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CZPhone::make($number)->isValid())->toBeTrue();
})->with(['420600000000', '420700000000']);

test('is valid with the local key', function () {
    expect(CZPhone::make('600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CZPhone::make('420600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CZPhone::make('+420600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CZPhone::make('00420600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CZPhone::make('420600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CZPhone::make('420700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CZPhone::make('420 6-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('420600000000');
});

test('is not valid when too short', function () {
    expect(CZPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CZPhone::make('7000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CZPhone::make('999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CZPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CZPhone::make('600000000')->all())->toEqual(['+420600000000', '00420600000000', '420600000000']);
});

test('toArray mirrors all', function () {
    $phone = CZPhone::make('600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CZPhone::make('420600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('600000000');
});

test('config exposes the country schema', function () {
    $phone = CZPhone::make('600000000');
    expect($phone->config('key'))->toEqual('420')
        ->and($phone->config('code'))->toEqual('CZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CZPhone::make('420 6-00000000')->number())->toEqual('420 6-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CZPhone::make('600000000');
    expect($phone->withPlus()->toString())->toEqual('+420600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('420600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CZPhone::make('600000000')->toString())->toEqual('+420600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '600000000'], ['phone' => CZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '60000000'], ['phone' => CZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '600000000'], ['phone' => CZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
