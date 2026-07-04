<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GLPhone;
use MMAE\Phones\Placeholders\GLPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GLPhoneRule;

test('can create a phone object', function () {
    expect(GLPhone::make('200000'))->toBeInstanceOf(GLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GLPhone::make($number)->isValid())->toBeTrue();
})->with(['299200000', '299400000', '299500000']);

test('is valid with the local key', function () {
    expect(GLPhone::make('200000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GLPhone::make('299200000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GLPhone::make('+299200000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GLPhone::make('00299200000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GLPhone::make('299200000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GLPhone::make('299500000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GLPhone::make('299 2-00000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('299200000');
});

test('is not valid when too short', function () {
    expect(GLPhone::make('20000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GLPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GLPhone::make('999200000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GLPhone::make('000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GLPhone::make('200000')->all())->toEqual(['+299200000', '00299200000', '299200000']);
});

test('toArray mirrors all', function () {
    $phone = GLPhone::make('200000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GLPhone::make('299200000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000');
});

test('config exposes the country schema', function () {
    $phone = GLPhone::make('200000');
    expect($phone->config('key'))->toEqual('299')
        ->and($phone->config('code'))->toEqual('GL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GLPhone::make('299 2-00000')->number())->toEqual('299 2-00000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GLPhone::make('200000');
    expect($phone->withPlus()->toString())->toEqual('+299200000')
        ->and($phone->withoutPlus()->toString())->toEqual('299200000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GLPhone::make('200000')->toString())->toEqual('+299200000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '200000'], ['phone' => GLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000'], ['phone' => GLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000'], ['phone' => GLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000'], ['phone' => GLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '200000'], ['phone' => GLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000'], ['phone' => GLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
