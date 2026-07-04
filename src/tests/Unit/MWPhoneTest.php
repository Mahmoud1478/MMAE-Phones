<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MWPhone;
use MMAE\Phones\Placeholders\MWPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MWPhoneRule;

test('can create a phone object', function () {
    expect(MWPhone::make('0800000000'))->toBeInstanceOf(MWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MWPhone::make($number)->isValid())->toBeTrue();
})->with(['265800000000', '265900000000']);

test('is valid with the local key', function () {
    expect(MWPhone::make('0800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MWPhone::make('265800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MWPhone::make('+265800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MWPhone::make('00265800000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MWPhone::make('265800000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MWPhone::make('265900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MWPhone::make('265 8-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('265800000000');
});

test('is not valid when too short', function () {
    expect(MWPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MWPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MWPhone::make('999800000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MWPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MWPhone::make('0800000000')->all())->toEqual(['+265800000000', '00265800000000', '265800000000', '0800000000']);
});

test('toArray mirrors all', function () {
    $phone = MWPhone::make('0800000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MWPhone::make('265800000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('800000000');
});

test('config exposes the country schema', function () {
    $phone = MWPhone::make('0800000000');
    expect($phone->config('key'))->toEqual('265')
        ->and($phone->config('code'))->toEqual('MW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MWPhone::make('265 8-00000000')->number())->toEqual('265 8-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MWPhone::make('0800000000');
    expect($phone->withPlus()->toString())->toEqual('+265800000000')
        ->and($phone->withoutPlus()->toString())->toEqual('265800000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MWPhone::make('0800000000')->toString())->toEqual('+265800000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0800000000'], ['phone' => MWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => MWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '80000000'], ['phone' => MWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '80000000'], ['phone' => MWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0800000000'], ['phone' => MWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => MWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
