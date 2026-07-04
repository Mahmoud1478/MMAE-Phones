<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GWPhone;
use MMAE\Phones\Placeholders\GWPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GWPhoneRule;

test('can create a phone object', function () {
    expect(GWPhone::make('950000000'))->toBeInstanceOf(GWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GWPhone::make($number)->isValid())->toBeTrue();
})->with(['245950000000', '245960000000', '245970000000']);

test('is valid with the local key', function () {
    expect(GWPhone::make('950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GWPhone::make('245950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GWPhone::make('+245950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GWPhone::make('00245950000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GWPhone::make('245950000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GWPhone::make('245970000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GWPhone::make('245 9-50000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('245950000000');
});

test('is not valid when too short', function () {
    expect(GWPhone::make('95000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GWPhone::make('9700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GWPhone::make('999950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GWPhone::make('050000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GWPhone::make('950000000')->all())->toEqual(['+245950000000', '00245950000000', '245950000000']);
});

test('toArray mirrors all', function () {
    $phone = GWPhone::make('950000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GWPhone::make('245950000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('950000000');
});

test('config exposes the country schema', function () {
    $phone = GWPhone::make('950000000');
    expect($phone->config('key'))->toEqual('245')
        ->and($phone->config('code'))->toEqual('GW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GWPhone::make('245 9-50000000')->number())->toEqual('245 9-50000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GWPhone::make('950000000');
    expect($phone->withPlus()->toString())->toEqual('+245950000000')
        ->and($phone->withoutPlus()->toString())->toEqual('245950000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GWPhone::make('950000000')->toString())->toEqual('+245950000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '950000000'], ['phone' => GWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '95000000'], ['phone' => GWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '95000000'], ['phone' => GWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '95000000'], ['phone' => GWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '950000000'], ['phone' => GWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '95000000'], ['phone' => GWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
