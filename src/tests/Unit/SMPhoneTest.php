<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SMPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SMPlaceholder;
use MMAE\Phones\Rules\SMPhoneRule;

test('can create a phone object', function () {
    expect(SMPhone::make('60000000'))->toBeInstanceOf(SMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SMPhone::make($number)->isValid())->toBeTrue();
})->with(['37860000000', '37861000000', '37862000000', '37863000000', '37864000000', '37865000000', '37866000000', '37867000000', '37868000000', '37869000000']);

test('is valid with the local key', function () {
    expect(SMPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SMPhone::make('37860000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SMPhone::make('+37860000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SMPhone::make('0037860000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SMPhone::make('37860000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SMPhone::make('37869000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SMPhone::make('378 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('37860000000');
});

test('is not valid when too short', function () {
    expect(SMPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SMPhone::make('690000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SMPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SMPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SMPhone::make('60000000')->all())->toEqual(['+37860000000', '0037860000000', '37860000000']);
});

test('toArray mirrors all', function () {
    $phone = SMPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SMPhone::make('37860000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = SMPhone::make('60000000');
    expect($phone->config('key'))->toEqual('378')
        ->and($phone->config('code'))->toEqual('SM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SMPhone::make('378 6-0000000')->number())->toEqual('378 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SMPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+37860000000')
        ->and($phone->withoutPlus()->toString())->toEqual('37860000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SMPhone::make('60000000')->toString())->toEqual('+37860000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => SMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => SMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => SMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => SMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => SMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => SMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
