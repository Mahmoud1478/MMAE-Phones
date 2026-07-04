<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\DJPhone;
use MMAE\Phones\Placeholders\DJPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\DJPhoneRule;

test('can create a phone object', function () {
    expect(DJPhone::make('77000000'))->toBeInstanceOf(DJPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(DJPhone::make($number)->isValid())->toBeTrue();
})->with(['25377000000', '25378000000']);

test('is valid with the local key', function () {
    expect(DJPhone::make('77000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(DJPhone::make('25377000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(DJPhone::make('+25377000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(DJPhone::make('0025377000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(DJPhone::make('25377000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(DJPhone::make('25378000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = DJPhone::make('253 7-7000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('25377000000');
});

test('is not valid when too short', function () {
    expect(DJPhone::make('7700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(DJPhone::make('780000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(DJPhone::make('99977000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(DJPhone::make('07000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(DJPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(DJPhone::make('77000000')->all())->toEqual(['+25377000000', '0025377000000', '25377000000']);
});

test('toArray mirrors all', function () {
    $phone = DJPhone::make('77000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = DJPhone::make('25377000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('77000000');
});

test('config exposes the country schema', function () {
    $phone = DJPhone::make('77000000');
    expect($phone->config('key'))->toEqual('253')
        ->and($phone->config('code'))->toEqual('DJ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(DJPhone::make('253 7-7000000')->number())->toEqual('253 7-7000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = DJPhone::make('77000000');
    expect($phone->withPlus()->toString())->toEqual('+25377000000')
        ->and($phone->withoutPlus()->toString())->toEqual('25377000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(DJPhone::make('77000000')->toString())->toEqual('+25377000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '77000000'], ['phone' => DJPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7700000'], ['phone' => DJPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(DJPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7700000'], ['phone' => DJPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7700000'], ['phone' => DJPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '77000000'], ['phone' => DJPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7700000'], ['phone' => DJPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = DJPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(DJPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('DJ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(DJPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('DJ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(DJPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
