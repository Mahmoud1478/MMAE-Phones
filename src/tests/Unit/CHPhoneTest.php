<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CHPhone;
use MMAE\Phones\Placeholders\CHPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CHPhoneRule;

test('can create a phone object', function () {
    expect(CHPhone::make('0750000000'))->toBeInstanceOf(CHPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CHPhone::make($number)->isValid())->toBeTrue();
})->with(['41750000000', '41760000000', '41770000000', '41780000000', '41790000000']);

test('is valid with the local key', function () {
    expect(CHPhone::make('0750000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CHPhone::make('41750000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CHPhone::make('+41750000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CHPhone::make('0041750000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CHPhone::make('41750000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CHPhone::make('41790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CHPhone::make('41 7-50000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('41750000000');
});

test('is not valid when too short', function () {
    expect(CHPhone::make('75000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CHPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CHPhone::make('999750000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CHPhone::make('0050000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CHPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CHPhone::make('0750000000')->all())->toEqual(['+41750000000', '0041750000000', '41750000000', '0750000000']);
});

test('toArray mirrors all', function () {
    $phone = CHPhone::make('0750000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CHPhone::make('41750000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('750000000');
});

test('config exposes the country schema', function () {
    $phone = CHPhone::make('0750000000');
    expect($phone->config('key'))->toEqual('41')
        ->and($phone->config('code'))->toEqual('CH')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CHPhone::make('41 7-50000000')->number())->toEqual('41 7-50000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CHPhone::make('0750000000');
    expect($phone->withPlus()->toString())->toEqual('+41750000000')
        ->and($phone->withoutPlus()->toString())->toEqual('41750000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CHPhone::make('0750000000')->toString())->toEqual('+41750000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0750000000'], ['phone' => CHPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '75000000'], ['phone' => CHPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CHPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '75000000'], ['phone' => CHPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '75000000'], ['phone' => CHPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0750000000'], ['phone' => CHPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '75000000'], ['phone' => CHPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CHPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CHPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CH');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CHPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CH')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CHPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
