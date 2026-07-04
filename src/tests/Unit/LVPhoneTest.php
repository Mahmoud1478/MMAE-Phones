<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LVPhone;
use MMAE\Phones\Placeholders\LVPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LVPhoneRule;

test('can create a phone object', function () {
    expect(LVPhone::make('20000000'))->toBeInstanceOf(LVPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LVPhone::make($number)->isValid())->toBeTrue();
})->with(['37120000000']);

test('is valid with the local key', function () {
    expect(LVPhone::make('20000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LVPhone::make('37120000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LVPhone::make('+37120000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LVPhone::make('0037120000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LVPhone::make('37120000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LVPhone::make('37120000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LVPhone::make('371 2-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('37120000000');
});

test('is not valid when too short', function () {
    expect(LVPhone::make('2000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LVPhone::make('200000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LVPhone::make('99920000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LVPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LVPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LVPhone::make('20000000')->all())->toEqual(['+37120000000', '0037120000000', '37120000000']);
});

test('toArray mirrors all', function () {
    $phone = LVPhone::make('20000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LVPhone::make('37120000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('20000000');
});

test('config exposes the country schema', function () {
    $phone = LVPhone::make('20000000');
    expect($phone->config('key'))->toEqual('371')
        ->and($phone->config('code'))->toEqual('LV')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LVPhone::make('371 2-0000000')->number())->toEqual('371 2-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LVPhone::make('20000000');
    expect($phone->withPlus()->toString())->toEqual('+37120000000')
        ->and($phone->withoutPlus()->toString())->toEqual('37120000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LVPhone::make('20000000')->toString())->toEqual('+37120000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => LVPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => LVPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LVPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '2000000'], ['phone' => LVPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '2000000'], ['phone' => LVPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '20000000'], ['phone' => LVPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => LVPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LVPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LVPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LV');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LVPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LV')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LVPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
