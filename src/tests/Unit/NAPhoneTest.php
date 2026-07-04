<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NAPhone;
use MMAE\Phones\Placeholders\NAPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NAPhoneRule;

test('can create a phone object', function () {
    expect(NAPhone::make('0800000000'))->toBeInstanceOf(NAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NAPhone::make($number)->isValid())->toBeTrue();
})->with(['264800000000', '264810000000', '264820000000', '264830000000', '264840000000', '264850000000', '264860000000', '264870000000', '264880000000', '264890000000']);

test('is valid with the local key', function () {
    expect(NAPhone::make('0800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NAPhone::make('264800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NAPhone::make('+264800000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NAPhone::make('00264800000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NAPhone::make('264800000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NAPhone::make('264890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NAPhone::make('264 8-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('264800000000');
});

test('is not valid when too short', function () {
    expect(NAPhone::make('80000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NAPhone::make('8900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NAPhone::make('999800000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NAPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NAPhone::make('0800000000')->all())->toEqual(['+264800000000', '00264800000000', '264800000000', '0800000000']);
});

test('toArray mirrors all', function () {
    $phone = NAPhone::make('0800000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NAPhone::make('264800000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('800000000');
});

test('config exposes the country schema', function () {
    $phone = NAPhone::make('0800000000');
    expect($phone->config('key'))->toEqual('264')
        ->and($phone->config('code'))->toEqual('NA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NAPhone::make('264 8-00000000')->number())->toEqual('264 8-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NAPhone::make('0800000000');
    expect($phone->withPlus()->toString())->toEqual('+264800000000')
        ->and($phone->withoutPlus()->toString())->toEqual('264800000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NAPhone::make('0800000000')->toString())->toEqual('+264800000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0800000000'], ['phone' => NAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => NAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '80000000'], ['phone' => NAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '80000000'], ['phone' => NAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0800000000'], ['phone' => NAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => NAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
