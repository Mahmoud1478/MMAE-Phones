<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MNPhone;
use MMAE\Phones\Placeholders\MNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MNPhoneRule;

test('can create a phone object', function () {
    expect(MNPhone::make('080000000'))->toBeInstanceOf(MNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MNPhone::make($number)->isValid())->toBeTrue();
})->with(['97680000000', '97690000000']);

test('is valid with the local key', function () {
    expect(MNPhone::make('080000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MNPhone::make('97680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MNPhone::make('+97680000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MNPhone::make('0097680000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MNPhone::make('97680000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MNPhone::make('97690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MNPhone::make('976 8-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('97680000000');
});

test('is not valid when too short', function () {
    expect(MNPhone::make('8000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MNPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MNPhone::make('99980000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MNPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MNPhone::make('080000000')->all())->toEqual(['+97680000000', '0097680000000', '97680000000', '080000000']);
});

test('toArray mirrors all', function () {
    $phone = MNPhone::make('080000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MNPhone::make('97680000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('80000000');
});

test('config exposes the country schema', function () {
    $phone = MNPhone::make('080000000');
    expect($phone->config('key'))->toEqual('976')
        ->and($phone->config('code'))->toEqual('MN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MNPhone::make('976 8-0000000')->number())->toEqual('976 8-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MNPhone::make('080000000');
    expect($phone->withPlus()->toString())->toEqual('+97680000000')
        ->and($phone->withoutPlus()->toString())->toEqual('97680000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MNPhone::make('080000000')->toString())->toEqual('+97680000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '080000000'], ['phone' => MNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => MNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '8000000'], ['phone' => MNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '8000000'], ['phone' => MNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '080000000'], ['phone' => MNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => MNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
