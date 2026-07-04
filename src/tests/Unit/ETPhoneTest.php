<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ETPhone;
use MMAE\Phones\Placeholders\ETPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ETPhoneRule;

test('can create a phone object', function () {
    expect(ETPhone::make('0700000000'))->toBeInstanceOf(ETPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ETPhone::make($number)->isValid())->toBeTrue();
})->with(['251700000000', '251800000000', '251900000000']);

test('is valid with the local key', function () {
    expect(ETPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ETPhone::make('251700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ETPhone::make('+251700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ETPhone::make('00251700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ETPhone::make('251700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ETPhone::make('251900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ETPhone::make('251 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('251700000000');
});

test('is not valid when too short', function () {
    expect(ETPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ETPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ETPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ETPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ETPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ETPhone::make('0700000000')->all())->toEqual(['+251700000000', '00251700000000', '251700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = ETPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ETPhone::make('251700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = ETPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('251')
        ->and($phone->config('code'))->toEqual('ET')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ETPhone::make('251 7-00000000')->number())->toEqual('251 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ETPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+251700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('251700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ETPhone::make('0700000000')->toString())->toEqual('+251700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => ETPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => ETPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ETPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => ETPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => ETPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => ETPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => ETPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ETPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ETPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ET');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ETPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ET')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ETPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
