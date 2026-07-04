<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KEPhone;
use MMAE\Phones\Placeholders\KEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KEPhoneRule;

test('can create a phone object', function () {
    expect(KEPhone::make('0100000000'))->toBeInstanceOf(KEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KEPhone::make($number)->isValid())->toBeTrue();
})->with(['254100000000', '254700000000']);

test('is valid with the local key', function () {
    expect(KEPhone::make('0100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KEPhone::make('254100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KEPhone::make('+254100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KEPhone::make('00254100000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KEPhone::make('254100000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KEPhone::make('254700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KEPhone::make('254 1-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('254100000000');
});

test('is not valid when too short', function () {
    expect(KEPhone::make('10000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KEPhone::make('7000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KEPhone::make('999100000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KEPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KEPhone::make('0100000000')->all())->toEqual(['+254100000000', '00254100000000', '254100000000', '0100000000']);
});

test('toArray mirrors all', function () {
    $phone = KEPhone::make('0100000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KEPhone::make('254100000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('100000000');
});

test('config exposes the country schema', function () {
    $phone = KEPhone::make('0100000000');
    expect($phone->config('key'))->toEqual('254')
        ->and($phone->config('code'))->toEqual('KE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KEPhone::make('254 1-00000000')->number())->toEqual('254 1-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KEPhone::make('0100000000');
    expect($phone->withPlus()->toString())->toEqual('+254100000000')
        ->and($phone->withoutPlus()->toString())->toEqual('254100000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KEPhone::make('0100000000')->toString())->toEqual('+254100000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0100000000'], ['phone' => KEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '10000000'], ['phone' => KEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '10000000'], ['phone' => KEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '10000000'], ['phone' => KEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0100000000'], ['phone' => KEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '10000000'], ['phone' => KEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
