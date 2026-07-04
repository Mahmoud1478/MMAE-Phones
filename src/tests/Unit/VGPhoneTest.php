<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VGPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VGPlaceholder;
use MMAE\Phones\Rules\VGPhoneRule;

test('can create a phone object', function () {
    expect(VGPhone::make('12840000000'))->toBeInstanceOf(VGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VGPhone::make($number)->isValid())->toBeTrue();
})->with(['12840000000']);

test('is valid with the local key', function () {
    expect(VGPhone::make('12840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VGPhone::make('12840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VGPhone::make('+12840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VGPhone::make('0012840000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VGPhone::make('12840000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VGPhone::make('12840000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VGPhone::make('1 2-840000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('12840000000');
});

test('is not valid when too short', function () {
    expect(VGPhone::make('284000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VGPhone::make('28400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VGPhone::make('9992840000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VGPhone::make('10840000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VGPhone::make('12840000000')->all())->toEqual(['+12840000000', '0012840000000', '12840000000']);
});

test('toArray mirrors all', function () {
    $phone = VGPhone::make('12840000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VGPhone::make('12840000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2840000000');
});

test('config exposes the country schema', function () {
    $phone = VGPhone::make('12840000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('VG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VGPhone::make('1 2-840000000')->number())->toEqual('1 2-840000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VGPhone::make('12840000000');
    expect($phone->withPlus()->toString())->toEqual('+12840000000')
        ->and($phone->withoutPlus()->toString())->toEqual('12840000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VGPhone::make('12840000000')->toString())->toEqual('+12840000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '12840000000'], ['phone' => VGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '284000000'], ['phone' => VGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '284000000'], ['phone' => VGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '284000000'], ['phone' => VGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '12840000000'], ['phone' => VGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '284000000'], ['phone' => VGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
