<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NZPhone;
use MMAE\Phones\Placeholders\NZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NZPhoneRule;

test('can create a phone object', function () {
    expect(NZPhone::make('0200000000'))->toBeInstanceOf(NZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NZPhone::make($number)->isValid())->toBeTrue();
})->with(['64200000000', '64210000000', '64220000000', '64230000000', '64240000000', '64250000000', '64260000000', '64270000000', '64280000000', '64290000000', '642000000000', '642100000000', '642200000000', '642300000000', '642400000000', '642500000000', '642600000000', '642700000000', '642800000000', '642900000000']);

test('is valid with the local key', function () {
    expect(NZPhone::make('0200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NZPhone::make('64200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NZPhone::make('+64200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NZPhone::make('0064200000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NZPhone::make('64200000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NZPhone::make('642900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NZPhone::make('64 2-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('64200000000');
});

test('is not valid when too short', function () {
    expect(NZPhone::make('20000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NZPhone::make('29000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NZPhone::make('999200000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NZPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NZPhone::make('0200000000')->all())->toEqual(['+64200000000', '0064200000000', '64200000000', '0200000000']);
});

test('toArray mirrors all', function () {
    $phone = NZPhone::make('0200000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NZPhone::make('64200000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000000');
});

test('config exposes the country schema', function () {
    $phone = NZPhone::make('0200000000');
    expect($phone->config('key'))->toEqual('64')
        ->and($phone->config('code'))->toEqual('NZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NZPhone::make('64 2-00000000')->number())->toEqual('64 2-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NZPhone::make('0200000000');
    expect($phone->withPlus()->toString())->toEqual('+64200000000')
        ->and($phone->withoutPlus()->toString())->toEqual('64200000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NZPhone::make('0200000000')->toString())->toEqual('+64200000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0200000000'], ['phone' => NZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => NZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000000'], ['phone' => NZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000000'], ['phone' => NZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0200000000'], ['phone' => NZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => NZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
