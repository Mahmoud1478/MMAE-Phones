<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MXPhone;
use MMAE\Phones\Placeholders\MXPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MXPhoneRule;

test('can create a phone object', function () {
    expect(MXPhone::make('2000000000'))->toBeInstanceOf(MXPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MXPhone::make($number)->isValid())->toBeTrue();
})->with(['522000000000', '523000000000', '524000000000', '525000000000', '526000000000', '527000000000', '528000000000', '529000000000']);

test('is valid with the local key', function () {
    expect(MXPhone::make('2000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MXPhone::make('522000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MXPhone::make('+522000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MXPhone::make('00522000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MXPhone::make('522000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MXPhone::make('529000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MXPhone::make('52 2-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('522000000000');
});

test('is not valid when too short', function () {
    expect(MXPhone::make('200000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MXPhone::make('90000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MXPhone::make('9992000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MXPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MXPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MXPhone::make('2000000000')->all())->toEqual(['+522000000000', '00522000000000', '522000000000']);
});

test('toArray mirrors all', function () {
    $phone = MXPhone::make('2000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MXPhone::make('522000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2000000000');
});

test('config exposes the country schema', function () {
    $phone = MXPhone::make('2000000000');
    expect($phone->config('key'))->toEqual('52')
        ->and($phone->config('code'))->toEqual('MX')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MXPhone::make('52 2-000000000')->number())->toEqual('52 2-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MXPhone::make('2000000000');
    expect($phone->withPlus()->toString())->toEqual('+522000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('522000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MXPhone::make('2000000000')->toString())->toEqual('+522000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '2000000000'], ['phone' => MXPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '200000000'], ['phone' => MXPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MXPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '200000000'], ['phone' => MXPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '200000000'], ['phone' => MXPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '2000000000'], ['phone' => MXPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '200000000'], ['phone' => MXPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MXPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MXPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MX');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MXPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MX')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MXPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
