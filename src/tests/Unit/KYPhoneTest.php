<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KYPhone;
use MMAE\Phones\Placeholders\KYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KYPhoneRule;

test('can create a phone object', function () {
    expect(KYPhone::make('13450000000'))->toBeInstanceOf(KYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KYPhone::make($number)->isValid())->toBeTrue();
})->with(['13450000000']);

test('is valid with the local key', function () {
    expect(KYPhone::make('13450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KYPhone::make('13450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KYPhone::make('+13450000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KYPhone::make('0013450000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KYPhone::make('13450000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KYPhone::make('13450000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KYPhone::make('1 3-450000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('13450000000');
});

test('is not valid when too short', function () {
    expect(KYPhone::make('345000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KYPhone::make('34500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KYPhone::make('9993450000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KYPhone::make('10450000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KYPhone::make('13450000000')->all())->toEqual(['+13450000000', '0013450000000', '13450000000']);
});

test('toArray mirrors all', function () {
    $phone = KYPhone::make('13450000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KYPhone::make('13450000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3450000000');
});

test('config exposes the country schema', function () {
    $phone = KYPhone::make('13450000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('KY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KYPhone::make('1 3-450000000')->number())->toEqual('1 3-450000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KYPhone::make('13450000000');
    expect($phone->withPlus()->toString())->toEqual('+13450000000')
        ->and($phone->withoutPlus()->toString())->toEqual('13450000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KYPhone::make('13450000000')->toString())->toEqual('+13450000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '13450000000'], ['phone' => KYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '345000000'], ['phone' => KYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '345000000'], ['phone' => KYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '345000000'], ['phone' => KYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '13450000000'], ['phone' => KYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '345000000'], ['phone' => KYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
