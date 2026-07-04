<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LYPhone;
use MMAE\Phones\Placeholders\LYPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LYPhoneRule;

test('can create a phone object', function () {
    expect(LYPhone::make('0910000000'))->toBeInstanceOf(LYPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LYPhone::make($number)->isValid())->toBeTrue();
})->with(['218910000000', '218920000000', '218930000000', '218940000000', '218950000000']);

test('is valid with the local key', function () {
    expect(LYPhone::make('0910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LYPhone::make('218910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LYPhone::make('+218910000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LYPhone::make('00218910000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LYPhone::make('218910000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LYPhone::make('218950000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LYPhone::make('218 9-10000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('218910000000');
});

test('is not valid when too short', function () {
    expect(LYPhone::make('91000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LYPhone::make('9500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LYPhone::make('999910000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LYPhone::make('0010000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LYPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LYPhone::make('0910000000')->all())->toEqual(['+218910000000', '00218910000000', '218910000000', '0910000000']);
});

test('toArray mirrors all', function () {
    $phone = LYPhone::make('0910000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LYPhone::make('218910000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('910000000');
});

test('config exposes the country schema', function () {
    $phone = LYPhone::make('0910000000');
    expect($phone->config('key'))->toEqual('218')
        ->and($phone->config('code'))->toEqual('LY')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LYPhone::make('218 9-10000000')->number())->toEqual('218 9-10000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LYPhone::make('0910000000');
    expect($phone->withPlus()->toString())->toEqual('+218910000000')
        ->and($phone->withoutPlus()->toString())->toEqual('218910000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LYPhone::make('0910000000')->toString())->toEqual('+218910000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0910000000'], ['phone' => LYPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '91000000'], ['phone' => LYPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LYPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '91000000'], ['phone' => LYPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '91000000'], ['phone' => LYPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0910000000'], ['phone' => LYPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '91000000'], ['phone' => LYPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LYPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LYPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LY');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LYPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LY')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LYPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
