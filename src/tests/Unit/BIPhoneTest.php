<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BIPhone;
use MMAE\Phones\Placeholders\BIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BIPhoneRule;

test('can create a phone object', function () {
    expect(BIPhone::make('60000000'))->toBeInstanceOf(BIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BIPhone::make($number)->isValid())->toBeTrue();
})->with(['25760000000', '25770000000']);

test('is valid with the local key', function () {
    expect(BIPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BIPhone::make('25760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BIPhone::make('+25760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BIPhone::make('0025760000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BIPhone::make('25760000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BIPhone::make('25770000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BIPhone::make('257 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('25760000000');
});

test('is not valid when too short', function () {
    expect(BIPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BIPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BIPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BIPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BIPhone::make('60000000')->all())->toEqual(['+25760000000', '0025760000000', '25760000000']);
});

test('toArray mirrors all', function () {
    $phone = BIPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BIPhone::make('25760000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = BIPhone::make('60000000');
    expect($phone->config('key'))->toEqual('257')
        ->and($phone->config('code'))->toEqual('BI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BIPhone::make('257 6-0000000')->number())->toEqual('257 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BIPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+25760000000')
        ->and($phone->withoutPlus()->toString())->toEqual('25760000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BIPhone::make('60000000')->toString())->toEqual('+25760000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => BIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => BIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => BIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
