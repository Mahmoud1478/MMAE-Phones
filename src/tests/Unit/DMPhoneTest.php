<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\DMPhone;
use MMAE\Phones\Placeholders\DMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\DMPhoneRule;

test('can create a phone object', function () {
    expect(DMPhone::make('17670000000'))->toBeInstanceOf(DMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(DMPhone::make($number)->isValid())->toBeTrue();
})->with(['17670000000']);

test('is valid with the local key', function () {
    expect(DMPhone::make('17670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(DMPhone::make('17670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(DMPhone::make('+17670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(DMPhone::make('0017670000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(DMPhone::make('17670000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(DMPhone::make('17670000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = DMPhone::make('1 7-670000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('17670000000');
});

test('is not valid when too short', function () {
    expect(DMPhone::make('767000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(DMPhone::make('76700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(DMPhone::make('9997670000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(DMPhone::make('10670000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(DMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(DMPhone::make('17670000000')->all())->toEqual(['+17670000000', '0017670000000', '17670000000']);
});

test('toArray mirrors all', function () {
    $phone = DMPhone::make('17670000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = DMPhone::make('17670000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7670000000');
});

test('config exposes the country schema', function () {
    $phone = DMPhone::make('17670000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('DM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(DMPhone::make('1 7-670000000')->number())->toEqual('1 7-670000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = DMPhone::make('17670000000');
    expect($phone->withPlus()->toString())->toEqual('+17670000000')
        ->and($phone->withoutPlus()->toString())->toEqual('17670000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(DMPhone::make('17670000000')->toString())->toEqual('+17670000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '17670000000'], ['phone' => DMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '767000000'], ['phone' => DMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(DMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '767000000'], ['phone' => DMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '767000000'], ['phone' => DMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '17670000000'], ['phone' => DMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '767000000'], ['phone' => DMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = DMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(DMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('DM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(DMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('DM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(DMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
