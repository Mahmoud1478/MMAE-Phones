<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PFPhone;
use MMAE\Phones\Placeholders\PFPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\PFPhoneRule;

test('can create a phone object', function () {
    expect(PFPhone::make('87000000'))->toBeInstanceOf(PFPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PFPhone::make($number)->isValid())->toBeTrue();
})->with(['68987000000', '68988000000', '68989000000']);

test('is valid with the local key', function () {
    expect(PFPhone::make('87000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PFPhone::make('68987000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PFPhone::make('+68987000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PFPhone::make('0068987000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PFPhone::make('68987000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PFPhone::make('68989000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PFPhone::make('689 8-7000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('68987000000');
});

test('is not valid when too short', function () {
    expect(PFPhone::make('8700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PFPhone::make('890000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PFPhone::make('99987000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PFPhone::make('07000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PFPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PFPhone::make('87000000')->all())->toEqual(['+68987000000', '0068987000000', '68987000000']);
});

test('toArray mirrors all', function () {
    $phone = PFPhone::make('87000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PFPhone::make('68987000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('87000000');
});

test('config exposes the country schema', function () {
    $phone = PFPhone::make('87000000');
    expect($phone->config('key'))->toEqual('689')
        ->and($phone->config('code'))->toEqual('PF')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PFPhone::make('689 8-7000000')->number())->toEqual('689 8-7000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PFPhone::make('87000000');
    expect($phone->withPlus()->toString())->toEqual('+68987000000')
        ->and($phone->withoutPlus()->toString())->toEqual('68987000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PFPhone::make('87000000')->toString())->toEqual('+68987000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '87000000'], ['phone' => PFPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '8700000'], ['phone' => PFPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PFPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '8700000'], ['phone' => PFPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '8700000'], ['phone' => PFPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '87000000'], ['phone' => PFPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '8700000'], ['phone' => PFPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PFPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PFPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PF');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PFPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PF')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PFPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
