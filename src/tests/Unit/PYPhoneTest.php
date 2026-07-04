<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PYPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\PYPlaceholder;
use MMAE\Phones\Rules\PYPhoneRule;

test('can create a phone object', function () {
    expect(PYPhone::make('0960000000'))->toBeInstanceOf(PYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PYPhone::make($number)->isValid())->toBeTrue();
})->with(['595960000000', '595970000000', '595980000000', '595990000000']);

test('is valid with the local key', function () {
    expect(PYPhone::make('0960000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PYPhone::make('595960000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PYPhone::make('+595960000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PYPhone::make('00595960000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PYPhone::make('595960000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PYPhone::make('595990000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PYPhone::make('595 9-60000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('595960000000');
});

test('is not valid when too short', function () {
    expect(PYPhone::make('96000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PYPhone::make('9900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PYPhone::make('999960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PYPhone::make('0060000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PYPhone::make('0960000000')->all())->toEqual(['+595960000000', '00595960000000', '595960000000', '0960000000']);
});

test('toArray mirrors all', function () {
    $phone = PYPhone::make('0960000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PYPhone::make('595960000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('960000000');
});

test('config exposes the country schema', function () {
    $phone = PYPhone::make('0960000000');
    expect($phone->config('key'))->toEqual('595')
        ->and($phone->config('code'))->toEqual('PY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PYPhone::make('595 9-60000000')->number())->toEqual('595 9-60000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PYPhone::make('0960000000');
    expect($phone->withPlus()->toString())->toEqual('+595960000000')
        ->and($phone->withoutPlus()->toString())->toEqual('595960000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PYPhone::make('0960000000')->toString())->toEqual('+595960000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0960000000'], ['phone' => PYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '96000000'], ['phone' => PYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '96000000'], ['phone' => PYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '96000000'], ['phone' => PYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0960000000'], ['phone' => PYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '96000000'], ['phone' => PYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
