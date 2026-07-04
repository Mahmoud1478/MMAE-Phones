<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AUPhone;
use MMAE\Phones\Placeholders\AUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AUPhoneRule;

test('can create a phone object', function () {
    expect(AUPhone::make('0400000000'))->toBeInstanceOf(AUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AUPhone::make($number)->isValid())->toBeTrue();
})->with(['61400000000', '61410000000', '61420000000', '61430000000', '61440000000', '61450000000', '61460000000', '61470000000', '61480000000', '61490000000']);

test('is valid with the local key', function () {
    expect(AUPhone::make('0400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AUPhone::make('61400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AUPhone::make('+61400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AUPhone::make('0061400000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AUPhone::make('61400000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AUPhone::make('61490000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AUPhone::make('61 4-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('61400000000');
});

test('is not valid when too short', function () {
    expect(AUPhone::make('40000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AUPhone::make('4900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AUPhone::make('999400000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AUPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AUPhone::make('0400000000')->all())->toEqual(['+61400000000', '0061400000000', '61400000000', '0400000000']);
});

test('toArray mirrors all', function () {
    $phone = AUPhone::make('0400000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AUPhone::make('61400000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('400000000');
});

test('config exposes the country schema', function () {
    $phone = AUPhone::make('0400000000');
    expect($phone->config('key'))->toEqual('61')
        ->and($phone->config('code'))->toEqual('AU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AUPhone::make('61 4-00000000')->number())->toEqual('61 4-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AUPhone::make('0400000000');
    expect($phone->withPlus()->toString())->toEqual('+61400000000')
        ->and($phone->withoutPlus()->toString())->toEqual('61400000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AUPhone::make('0400000000')->toString())->toEqual('+61400000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0400000000'], ['phone' => AUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '40000000'], ['phone' => AUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0400000000'], ['phone' => AUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '40000000'], ['phone' => AUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
