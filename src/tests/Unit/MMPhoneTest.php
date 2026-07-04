<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MMPhone;
use MMAE\Phones\Placeholders\MMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MMPhoneRule;

test('can create a phone object', function () {
    expect(MMPhone::make('090000000'))->toBeInstanceOf(MMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MMPhone::make($number)->isValid())->toBeTrue();
})->with(['9590000000', '95900000000']);

test('is valid with the local key', function () {
    expect(MMPhone::make('090000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MMPhone::make('9590000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MMPhone::make('+9590000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MMPhone::make('009590000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MMPhone::make('9590000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MMPhone::make('95900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MMPhone::make('95 9-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('9590000000');
});

test('is not valid when too short', function () {
    expect(MMPhone::make('9000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MMPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MMPhone::make('99990000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MMPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MMPhone::make('090000000')->all())->toEqual(['+9590000000', '009590000000', '9590000000', '090000000']);
});

test('toArray mirrors all', function () {
    $phone = MMPhone::make('090000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MMPhone::make('9590000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('90000000');
});

test('config exposes the country schema', function () {
    $phone = MMPhone::make('090000000');
    expect($phone->config('key'))->toEqual('95')
        ->and($phone->config('code'))->toEqual('MM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MMPhone::make('95 9-0000000')->number())->toEqual('95 9-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MMPhone::make('090000000');
    expect($phone->withPlus()->toString())->toEqual('+9590000000')
        ->and($phone->withoutPlus()->toString())->toEqual('9590000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MMPhone::make('090000000')->toString())->toEqual('+9590000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '090000000'], ['phone' => MMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => MMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '9000000'], ['phone' => MMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '9000000'], ['phone' => MMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '090000000'], ['phone' => MMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => MMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
