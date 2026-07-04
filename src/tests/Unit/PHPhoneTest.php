<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PHPhone;
use MMAE\Phones\Placeholders\PHPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\PHPhoneRule;

test('can create a phone object', function () {
    expect(PHPhone::make('09000000000'))->toBeInstanceOf(PHPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PHPhone::make($number)->isValid())->toBeTrue();
})->with(['639000000000', '639100000000', '639200000000', '639300000000', '639400000000', '639500000000', '639600000000', '639700000000', '639800000000', '639900000000']);

test('is valid with the local key', function () {
    expect(PHPhone::make('09000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PHPhone::make('639000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PHPhone::make('+639000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PHPhone::make('00639000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PHPhone::make('639000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PHPhone::make('639900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PHPhone::make('63 9-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('639000000000');
});

test('is not valid when too short', function () {
    expect(PHPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PHPhone::make('99000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PHPhone::make('9999000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PHPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PHPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PHPhone::make('09000000000')->all())->toEqual(['+639000000000', '00639000000000', '639000000000', '09000000000']);
});

test('toArray mirrors all', function () {
    $phone = PHPhone::make('09000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PHPhone::make('639000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('9000000000');
});

test('config exposes the country schema', function () {
    $phone = PHPhone::make('09000000000');
    expect($phone->config('key'))->toEqual('63')
        ->and($phone->config('code'))->toEqual('PH')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PHPhone::make('63 9-000000000')->number())->toEqual('63 9-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PHPhone::make('09000000000');
    expect($phone->withPlus()->toString())->toEqual('+639000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('639000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PHPhone::make('09000000000')->toString())->toEqual('+639000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '09000000000'], ['phone' => PHPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => PHPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PHPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '900000000'], ['phone' => PHPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '900000000'], ['phone' => PHPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '09000000000'], ['phone' => PHPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => PHPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PHPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PHPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PH');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PHPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PH')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PHPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
