<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LKPhone;
use MMAE\Phones\Placeholders\LKPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LKPhoneRule;

test('can create a phone object', function () {
    expect(LKPhone::make('0700000000'))->toBeInstanceOf(LKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LKPhone::make($number)->isValid())->toBeTrue();
})->with(['94700000000', '94710000000', '94720000000', '94740000000', '94750000000', '94760000000', '94770000000', '94780000000']);

test('is valid with the local key', function () {
    expect(LKPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LKPhone::make('94700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LKPhone::make('+94700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LKPhone::make('0094700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LKPhone::make('94700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LKPhone::make('94780000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LKPhone::make('94 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('94700000000');
});

test('is not valid when too short', function () {
    expect(LKPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LKPhone::make('7800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LKPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LKPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LKPhone::make('0700000000')->all())->toEqual(['+94700000000', '0094700000000', '94700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = LKPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LKPhone::make('94700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = LKPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('94')
        ->and($phone->config('code'))->toEqual('LK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LKPhone::make('94 7-00000000')->number())->toEqual('94 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LKPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+94700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('94700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LKPhone::make('0700000000')->toString())->toEqual('+94700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => LKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => LKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => LKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => LKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => LKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => LKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
