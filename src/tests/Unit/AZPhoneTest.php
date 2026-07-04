<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AZPhone;
use MMAE\Phones\Placeholders\AZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AZPhoneRule;

test('can create a phone object', function () {
    expect(AZPhone::make('0400000000'))->toBeInstanceOf(AZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AZPhone::make($number)->isValid())->toBeTrue();
})->with(['994400000000', '994410000000', '994420000000', '994430000000', '994440000000', '994450000000', '994460000000', '994470000000', '994480000000', '994490000000', '994500000000', '994510000000', '994520000000', '994530000000', '994540000000', '994550000000', '994560000000', '994570000000', '994580000000', '994590000000', '994600000000', '994610000000', '994620000000', '994630000000', '994640000000', '994650000000', '994660000000', '994670000000', '994680000000', '994690000000']);

test('is valid with the local key', function () {
    expect(AZPhone::make('0400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AZPhone::make('994400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AZPhone::make('+994400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AZPhone::make('00994400000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AZPhone::make('994400000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AZPhone::make('994690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AZPhone::make('994 4-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('994400000000');
});

test('is not valid when too short', function () {
    expect(AZPhone::make('40000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AZPhone::make('6900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AZPhone::make('999400000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AZPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AZPhone::make('0400000000')->all())->toEqual(['+994400000000', '00994400000000', '994400000000', '0400000000']);
});

test('toArray mirrors all', function () {
    $phone = AZPhone::make('0400000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AZPhone::make('994400000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('400000000');
});

test('config exposes the country schema', function () {
    $phone = AZPhone::make('0400000000');
    expect($phone->config('key'))->toEqual('994')
        ->and($phone->config('code'))->toEqual('AZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AZPhone::make('994 4-00000000')->number())->toEqual('994 4-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AZPhone::make('0400000000');
    expect($phone->withPlus()->toString())->toEqual('+994400000000')
        ->and($phone->withoutPlus()->toString())->toEqual('994400000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AZPhone::make('0400000000')->toString())->toEqual('+994400000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0400000000'], ['phone' => AZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '40000000'], ['phone' => AZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0400000000'], ['phone' => AZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
