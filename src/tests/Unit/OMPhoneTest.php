<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\OMPhone;
use MMAE\Phones\Placeholders\OMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\OMPhoneRule;

test('can create a phone object', function () {
    expect(OMPhone::make('070000000'))->toBeInstanceOf(OMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(OMPhone::make($number)->isValid())->toBeTrue();
})->with(['96870000000', '96890000000']);

test('is valid with the local key', function () {
    expect(OMPhone::make('070000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(OMPhone::make('96870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(OMPhone::make('+96870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(OMPhone::make('0096870000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(OMPhone::make('96870000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(OMPhone::make('96890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = OMPhone::make('968 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('96870000000');
});

test('is not valid when too short', function () {
    expect(OMPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(OMPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(OMPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(OMPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(OMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(OMPhone::make('070000000')->all())->toEqual(['+96870000000', '0096870000000', '96870000000', '070000000']);
});

test('toArray mirrors all', function () {
    $phone = OMPhone::make('070000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = OMPhone::make('96870000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = OMPhone::make('070000000');
    expect($phone->config('key'))->toEqual('968')
        ->and($phone->config('code'))->toEqual('OM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(OMPhone::make('968 7-0000000')->number())->toEqual('968 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = OMPhone::make('070000000');
    expect($phone->withPlus()->toString())->toEqual('+96870000000')
        ->and($phone->withoutPlus()->toString())->toEqual('96870000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(OMPhone::make('070000000')->toString())->toEqual('+96870000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '070000000'], ['phone' => OMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => OMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(OMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => OMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => OMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '070000000'], ['phone' => OMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => OMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = OMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(OMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('OM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(OMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('OM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(OMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
