<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\UYPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\UYPlaceholder;
use MMAE\Phones\Rules\UYPhoneRule;

test('can create a phone object', function () {
    expect(UYPhone::make('090000000'))->toBeInstanceOf(UYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(UYPhone::make($number)->isValid())->toBeTrue();
})->with(['59890000000']);

test('is valid with the local key', function () {
    expect(UYPhone::make('090000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(UYPhone::make('59890000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(UYPhone::make('+59890000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(UYPhone::make('0059890000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(UYPhone::make('59890000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(UYPhone::make('59890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = UYPhone::make('598 9-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('59890000000');
});

test('is not valid when too short', function () {
    expect(UYPhone::make('9000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(UYPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(UYPhone::make('99990000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(UYPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(UYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(UYPhone::make('090000000')->all())->toEqual(['+59890000000', '0059890000000', '59890000000', '090000000']);
});

test('toArray mirrors all', function () {
    $phone = UYPhone::make('090000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = UYPhone::make('59890000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('90000000');
});

test('config exposes the country schema', function () {
    $phone = UYPhone::make('090000000');
    expect($phone->config('key'))->toEqual('598')
        ->and($phone->config('code'))->toEqual('UY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(UYPhone::make('598 9-0000000')->number())->toEqual('598 9-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = UYPhone::make('090000000');
    expect($phone->withPlus()->toString())->toEqual('+59890000000')
        ->and($phone->withoutPlus()->toString())->toEqual('59890000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(UYPhone::make('090000000')->toString())->toEqual('+59890000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '090000000'], ['phone' => UYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => UYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(UYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '9000000'], ['phone' => UYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '9000000'], ['phone' => UYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '090000000'], ['phone' => UYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => UYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = UYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(UYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('UY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(UYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('UY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(UYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
