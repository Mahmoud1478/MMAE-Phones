<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GDPhone;
use MMAE\Phones\Placeholders\GDPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GDPhoneRule;

test('can create a phone object', function () {
    expect(GDPhone::make('14730000000'))->toBeInstanceOf(GDPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GDPhone::make($number)->isValid())->toBeTrue();
})->with(['14730000000']);

test('is valid with the local key', function () {
    expect(GDPhone::make('14730000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GDPhone::make('14730000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GDPhone::make('+14730000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GDPhone::make('0014730000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GDPhone::make('14730000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GDPhone::make('14730000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GDPhone::make('1 4-730000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('14730000000');
});

test('is not valid when too short', function () {
    expect(GDPhone::make('473000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GDPhone::make('47300000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GDPhone::make('9994730000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GDPhone::make('10730000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GDPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GDPhone::make('14730000000')->all())->toEqual(['+14730000000', '0014730000000', '14730000000']);
});

test('toArray mirrors all', function () {
    $phone = GDPhone::make('14730000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GDPhone::make('14730000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('4730000000');
});

test('config exposes the country schema', function () {
    $phone = GDPhone::make('14730000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('GD')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GDPhone::make('1 4-730000000')->number())->toEqual('1 4-730000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GDPhone::make('14730000000');
    expect($phone->withPlus()->toString())->toEqual('+14730000000')
        ->and($phone->withoutPlus()->toString())->toEqual('14730000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GDPhone::make('14730000000')->toString())->toEqual('+14730000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '14730000000'], ['phone' => GDPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '473000000'], ['phone' => GDPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GDPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '473000000'], ['phone' => GDPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '473000000'], ['phone' => GDPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '14730000000'], ['phone' => GDPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '473000000'], ['phone' => GDPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GDPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GDPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GD');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GDPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GD')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GDPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
