<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BOPhone;
use MMAE\Phones\Placeholders\BOPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BOPhoneRule;

test('can create a phone object', function () {
    expect(BOPhone::make('060000000'))->toBeInstanceOf(BOPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BOPhone::make($number)->isValid())->toBeTrue();
})->with(['59160000000', '59170000000']);

test('is valid with the local key', function () {
    expect(BOPhone::make('060000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BOPhone::make('59160000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BOPhone::make('+59160000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BOPhone::make('0059160000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BOPhone::make('59160000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BOPhone::make('59170000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BOPhone::make('591 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('59160000000');
});

test('is not valid when too short', function () {
    expect(BOPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BOPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BOPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BOPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BOPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BOPhone::make('060000000')->all())->toEqual(['+59160000000', '0059160000000', '59160000000', '060000000']);
});

test('toArray mirrors all', function () {
    $phone = BOPhone::make('060000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BOPhone::make('59160000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = BOPhone::make('060000000');
    expect($phone->config('key'))->toEqual('591')
        ->and($phone->config('code'))->toEqual('BO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BOPhone::make('591 6-0000000')->number())->toEqual('591 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BOPhone::make('060000000');
    expect($phone->withPlus()->toString())->toEqual('+59160000000')
        ->and($phone->withoutPlus()->toString())->toEqual('59160000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BOPhone::make('060000000')->toString())->toEqual('+59160000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '060000000'], ['phone' => BOPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BOPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BOPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BOPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => BOPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '060000000'], ['phone' => BOPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BOPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BOPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BOPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BOPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BOPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
