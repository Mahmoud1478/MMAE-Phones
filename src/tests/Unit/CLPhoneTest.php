<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CLPhone;
use MMAE\Phones\Placeholders\CLPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CLPhoneRule;

test('can create a phone object', function () {
    expect(CLPhone::make('900000000'))->toBeInstanceOf(CLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CLPhone::make($number)->isValid())->toBeTrue();
})->with(['56900000000']);

test('is valid with the local key', function () {
    expect(CLPhone::make('900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CLPhone::make('56900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CLPhone::make('+56900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CLPhone::make('0056900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CLPhone::make('56900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CLPhone::make('56900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CLPhone::make('56 9-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('56900000000');
});

test('is not valid when too short', function () {
    expect(CLPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CLPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CLPhone::make('999900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CLPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CLPhone::make('900000000')->all())->toEqual(['+56900000000', '0056900000000', '56900000000']);
});

test('toArray mirrors all', function () {
    $phone = CLPhone::make('900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CLPhone::make('56900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('900000000');
});

test('config exposes the country schema', function () {
    $phone = CLPhone::make('900000000');
    expect($phone->config('key'))->toEqual('56')
        ->and($phone->config('code'))->toEqual('CL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CLPhone::make('56 9-00000000')->number())->toEqual('56 9-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CLPhone::make('900000000');
    expect($phone->withPlus()->toString())->toEqual('+56900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('56900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CLPhone::make('900000000')->toString())->toEqual('+56900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => CLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => CLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '90000000'], ['phone' => CLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '90000000'], ['phone' => CLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '900000000'], ['phone' => CLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => CLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
