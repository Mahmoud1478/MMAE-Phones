<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GRPhone;
use MMAE\Phones\Placeholders\GRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GRPhoneRule;

test('can create a phone object', function () {
    expect(GRPhone::make('6600000000'))->toBeInstanceOf(GRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GRPhone::make($number)->isValid())->toBeTrue();
})->with(['306600000000', '306900000000']);

test('is valid with the local key', function () {
    expect(GRPhone::make('6600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GRPhone::make('306600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GRPhone::make('+306600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GRPhone::make('00306600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GRPhone::make('306600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GRPhone::make('306900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GRPhone::make('30 6-600000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('306600000000');
});

test('is not valid when too short', function () {
    expect(GRPhone::make('660000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GRPhone::make('69000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GRPhone::make('9996600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GRPhone::make('0600000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GRPhone::make('6600000000')->all())->toEqual(['+306600000000', '00306600000000', '306600000000']);
});

test('toArray mirrors all', function () {
    $phone = GRPhone::make('6600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GRPhone::make('306600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6600000000');
});

test('config exposes the country schema', function () {
    $phone = GRPhone::make('6600000000');
    expect($phone->config('key'))->toEqual('30')
        ->and($phone->config('code'))->toEqual('GR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GRPhone::make('30 6-600000000')->number())->toEqual('30 6-600000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GRPhone::make('6600000000');
    expect($phone->withPlus()->toString())->toEqual('+306600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('306600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GRPhone::make('6600000000')->toString())->toEqual('+306600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '6600000000'], ['phone' => GRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '660000000'], ['phone' => GRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '660000000'], ['phone' => GRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '660000000'], ['phone' => GRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '6600000000'], ['phone' => GRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '660000000'], ['phone' => GRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
