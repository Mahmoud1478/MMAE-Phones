<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ROPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\ROPlaceholder;
use MMAE\Phones\Rules\ROPhoneRule;

test('can create a phone object', function () {
    expect(ROPhone::make('0600000000'))->toBeInstanceOf(ROPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ROPhone::make($number)->isValid())->toBeTrue();
})->with(['40600000000', '40700000000']);

test('is valid with the local key', function () {
    expect(ROPhone::make('0600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ROPhone::make('40600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ROPhone::make('+40600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ROPhone::make('0040600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ROPhone::make('40600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ROPhone::make('40700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ROPhone::make('40 6-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('40600000000');
});

test('is not valid when too short', function () {
    expect(ROPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ROPhone::make('7000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ROPhone::make('999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ROPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ROPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ROPhone::make('0600000000')->all())->toEqual(['+40600000000', '0040600000000', '40600000000', '0600000000']);
});

test('toArray mirrors all', function () {
    $phone = ROPhone::make('0600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ROPhone::make('40600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('600000000');
});

test('config exposes the country schema', function () {
    $phone = ROPhone::make('0600000000');
    expect($phone->config('key'))->toEqual('40')
        ->and($phone->config('code'))->toEqual('RO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ROPhone::make('40 6-00000000')->number())->toEqual('40 6-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ROPhone::make('0600000000');
    expect($phone->withPlus()->toString())->toEqual('+40600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('40600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ROPhone::make('0600000000')->toString())->toEqual('+40600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0600000000'], ['phone' => ROPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ROPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ROPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ROPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '60000000'], ['phone' => ROPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0600000000'], ['phone' => ROPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ROPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ROPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ROPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('RO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ROPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('RO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ROPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
