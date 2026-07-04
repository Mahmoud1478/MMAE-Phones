<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CMPhone;
use MMAE\Phones\Placeholders\CMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CMPhoneRule;

test('can create a phone object', function () {
    expect(CMPhone::make('600000000'))->toBeInstanceOf(CMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CMPhone::make($number)->isValid())->toBeTrue();
})->with(['237600000000', '237610000000', '237620000000', '237630000000', '237640000000', '237650000000', '237660000000', '237670000000', '237680000000', '237690000000']);

test('is valid with the local key', function () {
    expect(CMPhone::make('600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CMPhone::make('237600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CMPhone::make('+237600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CMPhone::make('00237600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CMPhone::make('237600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CMPhone::make('237690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CMPhone::make('237 6-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('237600000000');
});

test('is not valid when too short', function () {
    expect(CMPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CMPhone::make('6900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CMPhone::make('999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CMPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CMPhone::make('600000000')->all())->toEqual(['+237600000000', '00237600000000', '237600000000']);
});

test('toArray mirrors all', function () {
    $phone = CMPhone::make('600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CMPhone::make('237600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('600000000');
});

test('config exposes the country schema', function () {
    $phone = CMPhone::make('600000000');
    expect($phone->config('key'))->toEqual('237')
        ->and($phone->config('code'))->toEqual('CM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CMPhone::make('237 6-00000000')->number())->toEqual('237 6-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CMPhone::make('600000000');
    expect($phone->withPlus()->toString())->toEqual('+237600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('237600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CMPhone::make('600000000')->toString())->toEqual('+237600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '600000000'], ['phone' => CMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '60000000'], ['phone' => CMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '600000000'], ['phone' => CMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => CMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
