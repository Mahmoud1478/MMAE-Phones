<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MCPhone;
use MMAE\Phones\Placeholders\MCPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MCPhoneRule;

test('can create a phone object', function () {
    expect(MCPhone::make('060000000'))->toBeInstanceOf(MCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MCPhone::make($number)->isValid())->toBeTrue();
})->with(['37760000000', '377600000000']);

test('is valid with the local key', function () {
    expect(MCPhone::make('060000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MCPhone::make('37760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MCPhone::make('+37760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MCPhone::make('0037760000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MCPhone::make('37760000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MCPhone::make('377600000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MCPhone::make('377 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('37760000000');
});

test('is not valid when too short', function () {
    expect(MCPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MCPhone::make('6000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MCPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MCPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MCPhone::make('060000000')->all())->toEqual(['+37760000000', '0037760000000', '37760000000', '060000000']);
});

test('toArray mirrors all', function () {
    $phone = MCPhone::make('060000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MCPhone::make('37760000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = MCPhone::make('060000000');
    expect($phone->config('key'))->toEqual('377')
        ->and($phone->config('code'))->toEqual('MC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MCPhone::make('377 6-0000000')->number())->toEqual('377 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MCPhone::make('060000000');
    expect($phone->withPlus()->toString())->toEqual('+37760000000')
        ->and($phone->withoutPlus()->toString())->toEqual('37760000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MCPhone::make('060000000')->toString())->toEqual('+37760000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '060000000'], ['phone' => MCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => MCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '060000000'], ['phone' => MCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
