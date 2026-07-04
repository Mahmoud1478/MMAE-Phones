<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GEPhone;
use MMAE\Phones\Placeholders\GEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GEPhoneRule;

test('can create a phone object', function () {
    expect(GEPhone::make('0500000000'))->toBeInstanceOf(GEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GEPhone::make($number)->isValid())->toBeTrue();
})->with(['995500000000', '995511000000', '995522000000', '995533000000', '995544000000', '995555000000', '995566000000', '995577000000', '995588000000', '995599000000']);

test('is valid with the local key', function () {
    expect(GEPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GEPhone::make('995500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GEPhone::make('+995500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GEPhone::make('00995500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GEPhone::make('995500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GEPhone::make('995599000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GEPhone::make('995 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('995500000000');
});

test('is not valid when too short', function () {
    expect(GEPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GEPhone::make('5990000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GEPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GEPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GEPhone::make('0500000000')->all())->toEqual(['+995500000000', '00995500000000', '995500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = GEPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GEPhone::make('995500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = GEPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('995')
        ->and($phone->config('code'))->toEqual('GE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GEPhone::make('995 5-00000000')->number())->toEqual('995 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GEPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+995500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('995500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GEPhone::make('0500000000')->toString())->toEqual('+995500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => GEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => GEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => GEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => GEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => GEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => GEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
