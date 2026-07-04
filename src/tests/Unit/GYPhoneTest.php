<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GYPhone;
use MMAE\Phones\Placeholders\GYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GYPhoneRule;

test('can create a phone object', function () {
    expect(GYPhone::make('6000000'))->toBeInstanceOf(GYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GYPhone::make($number)->isValid())->toBeTrue();
})->with(['5926000000']);

test('is valid with the local key', function () {
    expect(GYPhone::make('6000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GYPhone::make('5926000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GYPhone::make('+5926000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GYPhone::make('005926000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GYPhone::make('5926000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GYPhone::make('5926000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GYPhone::make('592 6-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('5926000000');
});

test('is not valid when too short', function () {
    expect(GYPhone::make('600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GYPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GYPhone::make('9996000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GYPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GYPhone::make('6000000')->all())->toEqual(['+5926000000', '005926000000', '5926000000']);
});

test('toArray mirrors all', function () {
    $phone = GYPhone::make('6000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GYPhone::make('5926000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6000000');
});

test('config exposes the country schema', function () {
    $phone = GYPhone::make('6000000');
    expect($phone->config('key'))->toEqual('592')
        ->and($phone->config('code'))->toEqual('GY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GYPhone::make('592 6-000000')->number())->toEqual('592 6-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GYPhone::make('6000000');
    expect($phone->withPlus()->toString())->toEqual('+5926000000')
        ->and($phone->withoutPlus()->toString())->toEqual('5926000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GYPhone::make('6000000')->toString())->toEqual('+5926000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => GYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => GYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '600000'], ['phone' => GYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '600000'], ['phone' => GYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '6000000'], ['phone' => GYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => GYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
