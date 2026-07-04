<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CRPhone;
use MMAE\Phones\Placeholders\CRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CRPhoneRule;

test('can create a phone object', function () {
    expect(CRPhone::make('60000000'))->toBeInstanceOf(CRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CRPhone::make($number)->isValid())->toBeTrue();
})->with(['50660000000', '50670000000', '50680000000']);

test('is valid with the local key', function () {
    expect(CRPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CRPhone::make('50660000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CRPhone::make('+50660000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CRPhone::make('0050660000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CRPhone::make('50660000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CRPhone::make('50680000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CRPhone::make('506 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('50660000000');
});

test('is not valid when too short', function () {
    expect(CRPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CRPhone::make('800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CRPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CRPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CRPhone::make('60000000')->all())->toEqual(['+50660000000', '0050660000000', '50660000000']);
});

test('toArray mirrors all', function () {
    $phone = CRPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CRPhone::make('50660000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = CRPhone::make('60000000');
    expect($phone->config('key'))->toEqual('506')
        ->and($phone->config('code'))->toEqual('CR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CRPhone::make('506 6-0000000')->number())->toEqual('506 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CRPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+50660000000')
        ->and($phone->withoutPlus()->toString())->toEqual('50660000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CRPhone::make('60000000')->toString())->toEqual('+50660000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => CRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => CRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => CRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => CRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => CRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
