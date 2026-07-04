<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CDPhone;
use MMAE\Phones\Placeholders\CDPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CDPhoneRule;

test('can create a phone object', function () {
    expect(CDPhone::make('0800000000'))->toBeInstanceOf(CDPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CDPhone::make($number)->isValid())->toBeTrue();
})->with(['243800000000', '243900000000']);

test('is valid with the local key', function () {
    expect(CDPhone::make('0800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CDPhone::make('243800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CDPhone::make('+243800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CDPhone::make('00243800000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CDPhone::make('243800000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CDPhone::make('243900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CDPhone::make('243 8-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('243800000000');
});

test('is not valid when too short', function () {
    expect(CDPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CDPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CDPhone::make('999800000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CDPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CDPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CDPhone::make('0800000000')->all())->toEqual(['+243800000000', '00243800000000', '243800000000', '0800000000']);
});

test('toArray mirrors all', function () {
    $phone = CDPhone::make('0800000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CDPhone::make('243800000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('800000000');
});

test('config exposes the country schema', function () {
    $phone = CDPhone::make('0800000000');
    expect($phone->config('key'))->toEqual('243')
        ->and($phone->config('code'))->toEqual('CD')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CDPhone::make('243 8-00000000')->number())->toEqual('243 8-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CDPhone::make('0800000000');
    expect($phone->withPlus()->toString())->toEqual('+243800000000')
        ->and($phone->withoutPlus()->toString())->toEqual('243800000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CDPhone::make('0800000000')->toString())->toEqual('+243800000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0800000000'], ['phone' => CDPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => CDPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CDPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '80000000'], ['phone' => CDPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '80000000'], ['phone' => CDPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0800000000'], ['phone' => CDPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => CDPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CDPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CDPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CD');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CDPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CD')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CDPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
