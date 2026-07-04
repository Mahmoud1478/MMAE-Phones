<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\HNPhone;
use MMAE\Phones\Placeholders\HNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\HNPhoneRule;

test('can create a phone object', function () {
    expect(HNPhone::make('30000000'))->toBeInstanceOf(HNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(HNPhone::make($number)->isValid())->toBeTrue();
})->with(['50430000000', '50440000000', '50450000000', '50460000000', '50470000000', '50480000000', '50490000000']);

test('is valid with the local key', function () {
    expect(HNPhone::make('30000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(HNPhone::make('50430000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(HNPhone::make('+50430000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(HNPhone::make('0050430000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(HNPhone::make('50430000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(HNPhone::make('50490000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = HNPhone::make('504 3-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('50430000000');
});

test('is not valid when too short', function () {
    expect(HNPhone::make('3000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(HNPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(HNPhone::make('99930000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(HNPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(HNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(HNPhone::make('30000000')->all())->toEqual(['+50430000000', '0050430000000', '50430000000']);
});

test('toArray mirrors all', function () {
    $phone = HNPhone::make('30000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = HNPhone::make('50430000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('30000000');
});

test('config exposes the country schema', function () {
    $phone = HNPhone::make('30000000');
    expect($phone->config('key'))->toEqual('504')
        ->and($phone->config('code'))->toEqual('HN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(HNPhone::make('504 3-0000000')->number())->toEqual('504 3-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = HNPhone::make('30000000');
    expect($phone->withPlus()->toString())->toEqual('+50430000000')
        ->and($phone->withoutPlus()->toString())->toEqual('50430000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(HNPhone::make('30000000')->toString())->toEqual('+50430000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '30000000'], ['phone' => HNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(HNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3000000'], ['phone' => HNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '30000000'], ['phone' => HNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = HNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(HNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('HN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(HNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('HN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(HNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
