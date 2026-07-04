<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ASPhone;
use MMAE\Phones\Placeholders\ASPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ASPhoneRule;

test('can create a phone object', function () {
    expect(ASPhone::make('16840000000'))->toBeInstanceOf(ASPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ASPhone::make($number)->isValid())->toBeTrue();
})->with(['16840000000']);

test('is valid with the local key', function () {
    expect(ASPhone::make('16840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ASPhone::make('16840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ASPhone::make('+16840000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ASPhone::make('0016840000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ASPhone::make('16840000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ASPhone::make('16840000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ASPhone::make('1 6-840000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16840000000');
});

test('is not valid when too short', function () {
    expect(ASPhone::make('684000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ASPhone::make('68400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ASPhone::make('9996840000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ASPhone::make('10840000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ASPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ASPhone::make('16840000000')->all())->toEqual(['+16840000000', '0016840000000', '16840000000']);
});

test('toArray mirrors all', function () {
    $phone = ASPhone::make('16840000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ASPhone::make('16840000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6840000000');
});

test('config exposes the country schema', function () {
    $phone = ASPhone::make('16840000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('AS')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ASPhone::make('1 6-840000000')->number())->toEqual('1 6-840000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ASPhone::make('16840000000');
    expect($phone->withPlus()->toString())->toEqual('+16840000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16840000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ASPhone::make('16840000000')->toString())->toEqual('+16840000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16840000000'], ['phone' => ASPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '684000000'], ['phone' => ASPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ASPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '684000000'], ['phone' => ASPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '684000000'], ['phone' => ASPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16840000000'], ['phone' => ASPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '684000000'], ['phone' => ASPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ASPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ASPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AS');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ASPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AS')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ASPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
