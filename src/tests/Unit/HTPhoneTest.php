<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\HTPhone;
use MMAE\Phones\Placeholders\HTPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\HTPhoneRule;

test('can create a phone object', function () {
    expect(HTPhone::make('30000000'))->toBeInstanceOf(HTPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(HTPhone::make($number)->isValid())->toBeTrue();
})->with(['50930000000', '50940000000']);

test('is valid with the local key', function () {
    expect(HTPhone::make('30000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(HTPhone::make('50930000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(HTPhone::make('+50930000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(HTPhone::make('0050930000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(HTPhone::make('50930000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(HTPhone::make('50940000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = HTPhone::make('509 3-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('50930000000');
});

test('is not valid when too short', function () {
    expect(HTPhone::make('3000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(HTPhone::make('400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(HTPhone::make('99930000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(HTPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(HTPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(HTPhone::make('30000000')->all())->toEqual(['+50930000000', '0050930000000', '50930000000']);
});

test('toArray mirrors all', function () {
    $phone = HTPhone::make('30000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = HTPhone::make('50930000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('30000000');
});

test('config exposes the country schema', function () {
    $phone = HTPhone::make('30000000');
    expect($phone->config('key'))->toEqual('509')
        ->and($phone->config('code'))->toEqual('HT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(HTPhone::make('509 3-0000000')->number())->toEqual('509 3-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = HTPhone::make('30000000');
    expect($phone->withPlus()->toString())->toEqual('+50930000000')
        ->and($phone->withoutPlus()->toString())->toEqual('50930000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(HTPhone::make('30000000')->toString())->toEqual('+50930000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '30000000'], ['phone' => HTPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HTPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(HTPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HTPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3000000'], ['phone' => HTPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '30000000'], ['phone' => HTPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => HTPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = HTPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(HTPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('HT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(HTPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('HT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(HTPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
