<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\UZPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\UZPlaceholder;
use MMAE\Phones\Rules\UZPhoneRule;

test('can create a phone object', function () {
    expect(UZPhone::make('900000000'))->toBeInstanceOf(UZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(UZPhone::make($number)->isValid())->toBeTrue();
})->with(['998900000000', '998910000000', '998920000000', '998930000000', '998940000000', '998950000000', '998960000000', '998970000000', '998980000000', '998990000000']);

test('is valid with the local key', function () {
    expect(UZPhone::make('900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(UZPhone::make('998900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(UZPhone::make('+998900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(UZPhone::make('00998900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(UZPhone::make('998900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(UZPhone::make('998990000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = UZPhone::make('998 9-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('998900000000');
});

test('is not valid when too short', function () {
    expect(UZPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(UZPhone::make('9900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(UZPhone::make('999900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(UZPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(UZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(UZPhone::make('900000000')->all())->toEqual(['+998900000000', '00998900000000', '998900000000']);
});

test('toArray mirrors all', function () {
    $phone = UZPhone::make('900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = UZPhone::make('998900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('900000000');
});

test('config exposes the country schema', function () {
    $phone = UZPhone::make('900000000');
    expect($phone->config('key'))->toEqual('998')
        ->and($phone->config('code'))->toEqual('UZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(UZPhone::make('998 9-00000000')->number())->toEqual('998 9-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = UZPhone::make('900000000');
    expect($phone->withPlus()->toString())->toEqual('+998900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('998900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(UZPhone::make('900000000')->toString())->toEqual('+998900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => UZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => UZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(UZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '90000000'], ['phone' => UZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '90000000'], ['phone' => UZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '900000000'], ['phone' => UZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => UZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = UZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(UZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('UZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(UZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('UZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(UZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
