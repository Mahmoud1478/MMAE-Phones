<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BSPhone;
use MMAE\Phones\Placeholders\BSPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BSPhoneRule;

test('can create a phone object', function () {
    expect(BSPhone::make('12420000000'))->toBeInstanceOf(BSPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BSPhone::make($number)->isValid())->toBeTrue();
})->with(['12420000000']);

test('is valid with the local key', function () {
    expect(BSPhone::make('12420000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BSPhone::make('12420000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BSPhone::make('+12420000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BSPhone::make('0012420000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BSPhone::make('12420000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BSPhone::make('12420000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BSPhone::make('1 2-420000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('12420000000');
});

test('is not valid when too short', function () {
    expect(BSPhone::make('242000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BSPhone::make('24200000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BSPhone::make('9992420000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BSPhone::make('10420000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BSPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BSPhone::make('12420000000')->all())->toEqual(['+12420000000', '0012420000000', '12420000000']);
});

test('toArray mirrors all', function () {
    $phone = BSPhone::make('12420000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BSPhone::make('12420000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2420000000');
});

test('config exposes the country schema', function () {
    $phone = BSPhone::make('12420000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('BS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BSPhone::make('1 2-420000000')->number())->toEqual('1 2-420000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BSPhone::make('12420000000');
    expect($phone->withPlus()->toString())->toEqual('+12420000000')
        ->and($phone->withoutPlus()->toString())->toEqual('12420000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BSPhone::make('12420000000')->toString())->toEqual('+12420000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '12420000000'], ['phone' => BSPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '242000000'], ['phone' => BSPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BSPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '242000000'], ['phone' => BSPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '242000000'], ['phone' => BSPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '12420000000'], ['phone' => BSPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '242000000'], ['phone' => BSPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BSPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BSPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BSPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BSPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
