<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NCPhone;
use MMAE\Phones\Placeholders\NCPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NCPhoneRule;

test('can create a phone object', function () {
    expect(NCPhone::make('700000'))->toBeInstanceOf(NCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NCPhone::make($number)->isValid())->toBeTrue();
})->with(['687700000', '687800000', '687900000']);

test('is valid with the local key', function () {
    expect(NCPhone::make('700000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NCPhone::make('687700000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NCPhone::make('+687700000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NCPhone::make('00687700000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NCPhone::make('687700000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NCPhone::make('687900000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NCPhone::make('687 7-00000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('687700000');
});

test('is not valid when too short', function () {
    expect(NCPhone::make('70000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NCPhone::make('9000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NCPhone::make('999700000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NCPhone::make('000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NCPhone::make('700000')->all())->toEqual(['+687700000', '00687700000', '687700000']);
});

test('toArray mirrors all', function () {
    $phone = NCPhone::make('700000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NCPhone::make('687700000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000');
});

test('config exposes the country schema', function () {
    $phone = NCPhone::make('700000');
    expect($phone->config('key'))->toEqual('687')
        ->and($phone->config('code'))->toEqual('NC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NCPhone::make('687 7-00000')->number())->toEqual('687 7-00000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NCPhone::make('700000');
    expect($phone->withPlus()->toString())->toEqual('+687700000')
        ->and($phone->withoutPlus()->toString())->toEqual('687700000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NCPhone::make('700000')->toString())->toEqual('+687700000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => NCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000'], ['phone' => NCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000'], ['phone' => NCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000'], ['phone' => NCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '700000'], ['phone' => NCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000'], ['phone' => NCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
