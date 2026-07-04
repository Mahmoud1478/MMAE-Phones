<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CVPhone;
use MMAE\Phones\Placeholders\CVPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CVPhoneRule;

test('can create a phone object', function () {
    expect(CVPhone::make('5000000'))->toBeInstanceOf(CVPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CVPhone::make($number)->isValid())->toBeTrue();
})->with(['2385000000', '2389000000']);

test('is valid with the local key', function () {
    expect(CVPhone::make('5000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CVPhone::make('2385000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CVPhone::make('+2385000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CVPhone::make('002385000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CVPhone::make('2385000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CVPhone::make('2389000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CVPhone::make('238 5-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2385000000');
});

test('is not valid when too short', function () {
    expect(CVPhone::make('500000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CVPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CVPhone::make('9995000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CVPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CVPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CVPhone::make('5000000')->all())->toEqual(['+2385000000', '002385000000', '2385000000']);
});

test('toArray mirrors all', function () {
    $phone = CVPhone::make('5000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CVPhone::make('2385000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('5000000');
});

test('config exposes the country schema', function () {
    $phone = CVPhone::make('5000000');
    expect($phone->config('key'))->toEqual('238')
        ->and($phone->config('code'))->toEqual('CV')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CVPhone::make('238 5-000000')->number())->toEqual('238 5-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CVPhone::make('5000000');
    expect($phone->withPlus()->toString())->toEqual('+2385000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2385000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CVPhone::make('5000000')->toString())->toEqual('+2385000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => CVPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '500000'], ['phone' => CVPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CVPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '500000'], ['phone' => CVPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '500000'], ['phone' => CVPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '5000000'], ['phone' => CVPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '500000'], ['phone' => CVPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CVPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CVPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CV');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CVPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CV')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CVPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
