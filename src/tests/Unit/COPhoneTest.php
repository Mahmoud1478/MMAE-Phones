<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\COPhone;
use MMAE\Phones\Placeholders\COPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\COPhoneRule;

test('can create a phone object', function () {
    expect(COPhone::make('03000000000'))->toBeInstanceOf(COPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(COPhone::make($number)->isValid())->toBeTrue();
})->with(['573000000000', '573100000000', '573200000000', '573300000000', '573400000000', '573500000000', '573600000000', '573700000000', '573800000000', '573900000000']);

test('is valid with the local key', function () {
    expect(COPhone::make('03000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(COPhone::make('573000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(COPhone::make('+573000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(COPhone::make('00573000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(COPhone::make('573000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(COPhone::make('573900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = COPhone::make('57 3-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('573000000000');
});

test('is not valid when too short', function () {
    expect(COPhone::make('300000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(COPhone::make('39000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(COPhone::make('9993000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(COPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(COPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(COPhone::make('03000000000')->all())->toEqual(['+573000000000', '00573000000000', '573000000000', '03000000000']);
});

test('toArray mirrors all', function () {
    $phone = COPhone::make('03000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = COPhone::make('573000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3000000000');
});

test('config exposes the country schema', function () {
    $phone = COPhone::make('03000000000');
    expect($phone->config('key'))->toEqual('57')
        ->and($phone->config('code'))->toEqual('CO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(COPhone::make('57 3-000000000')->number())->toEqual('57 3-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = COPhone::make('03000000000');
    expect($phone->withPlus()->toString())->toEqual('+573000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('573000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(COPhone::make('03000000000')->toString())->toEqual('+573000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '03000000000'], ['phone' => COPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => COPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(COPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '300000000'], ['phone' => COPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '300000000'], ['phone' => COPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '03000000000'], ['phone' => COPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => COPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = COPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(COPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(COPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(COPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
