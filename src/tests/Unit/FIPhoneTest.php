<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\FIPhone;
use MMAE\Phones\Placeholders\FIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\FIPhoneRule;

test('can create a phone object', function () {
    expect(FIPhone::make('0400000000'))->toBeInstanceOf(FIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(FIPhone::make($number)->isValid())->toBeTrue();
})->with(['358400000000', '358410000000', '358420000000', '358440000000', '358450000000', '358460000000', '358500000000', '358510000000', '358520000000', '358530000000', '358540000000', '358550000000', '358560000000', '358580000000', '358590000000', '358600000000', '3584000000000', '3584100000000', '3584200000000', '3584400000000', '3584500000000', '3584600000000', '3585000000000', '3585100000000', '3585200000000', '3585300000000', '3585400000000', '3585500000000', '3585600000000', '3585800000000', '3585900000000', '3586000000000']);

test('is valid with the local key', function () {
    expect(FIPhone::make('0400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(FIPhone::make('358400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(FIPhone::make('+358400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(FIPhone::make('00358400000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(FIPhone::make('358400000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(FIPhone::make('3586000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = FIPhone::make('358 4-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('358400000000');
});

test('is not valid when too short', function () {
    expect(FIPhone::make('40000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(FIPhone::make('60000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(FIPhone::make('999400000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(FIPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(FIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(FIPhone::make('0400000000')->all())->toEqual(['+358400000000', '00358400000000', '358400000000', '0400000000']);
});

test('toArray mirrors all', function () {
    $phone = FIPhone::make('0400000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = FIPhone::make('358400000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('400000000');
});

test('config exposes the country schema', function () {
    $phone = FIPhone::make('0400000000');
    expect($phone->config('key'))->toEqual('358')
        ->and($phone->config('code'))->toEqual('FI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(FIPhone::make('358 4-00000000')->number())->toEqual('358 4-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = FIPhone::make('0400000000');
    expect($phone->withPlus()->toString())->toEqual('+358400000000')
        ->and($phone->withoutPlus()->toString())->toEqual('358400000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(FIPhone::make('0400000000')->toString())->toEqual('+358400000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0400000000'], ['phone' => FIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => FIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(FIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '40000000'], ['phone' => FIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '40000000'], ['phone' => FIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0400000000'], ['phone' => FIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => FIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = FIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(FIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('FI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(FIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('FI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(FIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
