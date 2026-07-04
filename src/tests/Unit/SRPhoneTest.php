<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SRPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SRPlaceholder;
use MMAE\Phones\Rules\SRPhoneRule;

test('can create a phone object', function () {
    expect(SRPhone::make('6000000'))->toBeInstanceOf(SRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SRPhone::make($number)->isValid())->toBeTrue();
})->with(['5976000000', '5977000000', '5978000000']);

test('is valid with the local key', function () {
    expect(SRPhone::make('6000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SRPhone::make('5976000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SRPhone::make('+5976000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SRPhone::make('005976000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SRPhone::make('5976000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SRPhone::make('5978000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SRPhone::make('597 6-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('5976000000');
});

test('is not valid when too short', function () {
    expect(SRPhone::make('600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SRPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SRPhone::make('9996000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SRPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SRPhone::make('6000000')->all())->toEqual(['+5976000000', '005976000000', '5976000000']);
});

test('toArray mirrors all', function () {
    $phone = SRPhone::make('6000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SRPhone::make('5976000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6000000');
});

test('config exposes the country schema', function () {
    $phone = SRPhone::make('6000000');
    expect($phone->config('key'))->toEqual('597')
        ->and($phone->config('code'))->toEqual('SR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SRPhone::make('597 6-000000')->number())->toEqual('597 6-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SRPhone::make('6000000');
    expect($phone->withPlus()->toString())->toEqual('+5976000000')
        ->and($phone->withoutPlus()->toString())->toEqual('5976000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SRPhone::make('6000000')->toString())->toEqual('+5976000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => SRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => SRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '600000'], ['phone' => SRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '600000'], ['phone' => SRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '6000000'], ['phone' => SRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => SRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
