<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CYPhone;
use MMAE\Phones\Placeholders\CYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CYPhoneRule;

test('can create a phone object', function () {
    expect(CYPhone::make('90000000'))->toBeInstanceOf(CYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CYPhone::make($number)->isValid())->toBeTrue();
})->with(['35790000000', '35791000000', '35792000000', '35793000000', '35794000000', '35795000000', '35796000000', '35797000000', '35798000000', '35799000000']);

test('is valid with the local key', function () {
    expect(CYPhone::make('90000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CYPhone::make('35790000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CYPhone::make('+35790000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CYPhone::make('0035790000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CYPhone::make('35790000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CYPhone::make('35799000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CYPhone::make('357 9-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('35790000000');
});

test('is not valid when too short', function () {
    expect(CYPhone::make('9000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CYPhone::make('990000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CYPhone::make('99990000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CYPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CYPhone::make('90000000')->all())->toEqual(['+35790000000', '0035790000000', '35790000000']);
});

test('toArray mirrors all', function () {
    $phone = CYPhone::make('90000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CYPhone::make('35790000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('90000000');
});

test('config exposes the country schema', function () {
    $phone = CYPhone::make('90000000');
    expect($phone->config('key'))->toEqual('357')
        ->and($phone->config('code'))->toEqual('CY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CYPhone::make('357 9-0000000')->number())->toEqual('357 9-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CYPhone::make('90000000');
    expect($phone->withPlus()->toString())->toEqual('+35790000000')
        ->and($phone->withoutPlus()->toString())->toEqual('35790000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CYPhone::make('90000000')->toString())->toEqual('+35790000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '90000000'], ['phone' => CYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => CYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '9000000'], ['phone' => CYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '9000000'], ['phone' => CYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '90000000'], ['phone' => CYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '9000000'], ['phone' => CYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
