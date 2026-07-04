<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CGPhone;
use MMAE\Phones\Placeholders\CGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CGPhoneRule;

test('can create a phone object', function () {
    expect(CGPhone::make('010000000'))->toBeInstanceOf(CGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CGPhone::make($number)->isValid())->toBeTrue();
})->with(['242010000000', '242020000000', '242030000000', '242040000000', '242050000000', '242060000000']);

test('is valid with the local key', function () {
    expect(CGPhone::make('010000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CGPhone::make('242010000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CGPhone::make('+242010000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CGPhone::make('00242010000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CGPhone::make('242010000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CGPhone::make('242060000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CGPhone::make('242 0-10000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('242010000000');
});

test('is not valid when too short', function () {
    expect(CGPhone::make('01000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CGPhone::make('0600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CGPhone::make('999010000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CGPhone::make('110000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CGPhone::make('010000000')->all())->toEqual(['+242010000000', '00242010000000', '242010000000']);
});

test('toArray mirrors all', function () {
    $phone = CGPhone::make('010000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CGPhone::make('242010000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('010000000');
});

test('config exposes the country schema', function () {
    $phone = CGPhone::make('010000000');
    expect($phone->config('key'))->toEqual('242')
        ->and($phone->config('code'))->toEqual('CG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CGPhone::make('242 0-10000000')->number())->toEqual('242 0-10000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CGPhone::make('010000000');
    expect($phone->withPlus()->toString())->toEqual('+242010000000')
        ->and($phone->withoutPlus()->toString())->toEqual('242010000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CGPhone::make('010000000')->toString())->toEqual('+242010000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '010000000'], ['phone' => CGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '01000000'], ['phone' => CGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '01000000'], ['phone' => CGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '01000000'], ['phone' => CGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '010000000'], ['phone' => CGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '01000000'], ['phone' => CGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
