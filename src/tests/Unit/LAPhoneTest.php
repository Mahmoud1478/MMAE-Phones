<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LAPhone;
use MMAE\Phones\Placeholders\LAPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LAPhoneRule;

test('can create a phone object', function () {
    expect(LAPhone::make('0200000000'))->toBeInstanceOf(LAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LAPhone::make($number)->isValid())->toBeTrue();
})->with(['856200000000', '8562000000000']);

test('is valid with the local key', function () {
    expect(LAPhone::make('0200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LAPhone::make('856200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LAPhone::make('+856200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LAPhone::make('00856200000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LAPhone::make('856200000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LAPhone::make('8562000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LAPhone::make('856 2-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('856200000000');
});

test('is not valid when too short', function () {
    expect(LAPhone::make('20000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LAPhone::make('20000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LAPhone::make('999200000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LAPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LAPhone::make('0200000000')->all())->toEqual(['+856200000000', '00856200000000', '856200000000', '0200000000']);
});

test('toArray mirrors all', function () {
    $phone = LAPhone::make('0200000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LAPhone::make('856200000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000000');
});

test('config exposes the country schema', function () {
    $phone = LAPhone::make('0200000000');
    expect($phone->config('key'))->toEqual('856')
        ->and($phone->config('code'))->toEqual('LA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LAPhone::make('856 2-00000000')->number())->toEqual('856 2-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LAPhone::make('0200000000');
    expect($phone->withPlus()->toString())->toEqual('+856200000000')
        ->and($phone->withoutPlus()->toString())->toEqual('856200000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LAPhone::make('0200000000')->toString())->toEqual('+856200000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0200000000'], ['phone' => LAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => LAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000000'], ['phone' => LAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000000'], ['phone' => LAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0200000000'], ['phone' => LAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => LAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
