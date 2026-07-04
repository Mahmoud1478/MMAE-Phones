<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GTPhone;
use MMAE\Phones\Placeholders\GTPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GTPhoneRule;

test('can create a phone object', function () {
    expect(GTPhone::make('30000000'))->toBeInstanceOf(GTPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GTPhone::make($number)->isValid())->toBeTrue();
})->with(['50230000000', '50240000000', '50250000000']);

test('is valid with the local key', function () {
    expect(GTPhone::make('30000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GTPhone::make('50230000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GTPhone::make('+50230000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GTPhone::make('0050230000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GTPhone::make('50230000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GTPhone::make('50250000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GTPhone::make('502 3-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('50230000000');
});

test('is not valid when too short', function () {
    expect(GTPhone::make('3000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GTPhone::make('500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GTPhone::make('99930000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GTPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GTPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GTPhone::make('30000000')->all())->toEqual(['+50230000000', '0050230000000', '50230000000']);
});

test('toArray mirrors all', function () {
    $phone = GTPhone::make('30000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GTPhone::make('50230000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('30000000');
});

test('config exposes the country schema', function () {
    $phone = GTPhone::make('30000000');
    expect($phone->config('key'))->toEqual('502')
        ->and($phone->config('code'))->toEqual('GT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GTPhone::make('502 3-0000000')->number())->toEqual('502 3-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GTPhone::make('30000000');
    expect($phone->withPlus()->toString())->toEqual('+50230000000')
        ->and($phone->withoutPlus()->toString())->toEqual('50230000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GTPhone::make('30000000')->toString())->toEqual('+50230000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '30000000'], ['phone' => GTPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => GTPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GTPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3000000'], ['phone' => GTPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3000000'], ['phone' => GTPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '30000000'], ['phone' => GTPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => GTPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GTPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GTPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GTPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GTPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
