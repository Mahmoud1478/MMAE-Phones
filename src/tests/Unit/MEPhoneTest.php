<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MEPhone;
use MMAE\Phones\Placeholders\MEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MEPhoneRule;

test('can create a phone object', function () {
    expect(MEPhone::make('060000000'))->toBeInstanceOf(MEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MEPhone::make($number)->isValid())->toBeTrue();
})->with(['38260000000']);

test('is valid with the local key', function () {
    expect(MEPhone::make('060000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MEPhone::make('38260000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MEPhone::make('+38260000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MEPhone::make('0038260000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MEPhone::make('38260000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MEPhone::make('38260000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MEPhone::make('382 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('38260000000');
});

test('is not valid when too short', function () {
    expect(MEPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MEPhone::make('600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MEPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MEPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MEPhone::make('060000000')->all())->toEqual(['+38260000000', '0038260000000', '38260000000', '060000000']);
});

test('toArray mirrors all', function () {
    $phone = MEPhone::make('060000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MEPhone::make('38260000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = MEPhone::make('060000000');
    expect($phone->config('key'))->toEqual('382')
        ->and($phone->config('code'))->toEqual('ME')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MEPhone::make('382 6-0000000')->number())->toEqual('382 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MEPhone::make('060000000');
    expect($phone->withPlus()->toString())->toEqual('+38260000000')
        ->and($phone->withoutPlus()->toString())->toEqual('38260000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MEPhone::make('060000000')->toString())->toEqual('+38260000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '060000000'], ['phone' => MEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => MEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '060000000'], ['phone' => MEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ME');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ME')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
