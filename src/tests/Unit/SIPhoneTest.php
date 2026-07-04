<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SIPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SIPlaceholder;
use MMAE\Phones\Rules\SIPhoneRule;

test('can create a phone object', function () {
    expect(SIPhone::make('030000000'))->toBeInstanceOf(SIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SIPhone::make($number)->isValid())->toBeTrue();
})->with(['38630000000', '38631000000', '38640000000', '38641000000', '38651000000', '38664000000', '38668000000', '38670000000', '38671000000', '38673000000', '38677000000', '38678000000', '386300000000', '386310000000', '386400000000', '386410000000', '386510000000', '386640000000', '386680000000', '386700000000', '386710000000', '386730000000', '386770000000', '386780000000']);

test('is valid with the local key', function () {
    expect(SIPhone::make('030000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SIPhone::make('38630000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SIPhone::make('+38630000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SIPhone::make('0038630000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SIPhone::make('38630000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SIPhone::make('386780000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SIPhone::make('386 3-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('38630000000');
});

test('is not valid when too short', function () {
    expect(SIPhone::make('3000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SIPhone::make('7800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SIPhone::make('99930000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SIPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SIPhone::make('030000000')->all())->toEqual(['+38630000000', '0038630000000', '38630000000', '030000000']);
});

test('toArray mirrors all', function () {
    $phone = SIPhone::make('030000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SIPhone::make('38630000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('30000000');
});

test('config exposes the country schema', function () {
    $phone = SIPhone::make('030000000');
    expect($phone->config('key'))->toEqual('386')
        ->and($phone->config('code'))->toEqual('SI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SIPhone::make('386 3-0000000')->number())->toEqual('386 3-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SIPhone::make('030000000');
    expect($phone->withPlus()->toString())->toEqual('+38630000000')
        ->and($phone->withoutPlus()->toString())->toEqual('38630000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SIPhone::make('030000000')->toString())->toEqual('+38630000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '030000000'], ['phone' => SIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => SIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3000000'], ['phone' => SIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3000000'], ['phone' => SIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '030000000'], ['phone' => SIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => SIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
