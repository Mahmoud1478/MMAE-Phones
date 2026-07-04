<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\IQPhone;
use MMAE\Phones\Placeholders\IQPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\IQPhoneRule;

test('can create a phone object', function () {
    expect(IQPhone::make('07300000000'))->toBeInstanceOf(IQPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(IQPhone::make($number)->isValid())->toBeTrue();
})->with(['9647300000000', '9647400000000', '9647500000000', '9647600000000', '9647700000000', '9647800000000', '9647900000000']);

test('is valid with the local key', function () {
    expect(IQPhone::make('07300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(IQPhone::make('9647300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(IQPhone::make('+9647300000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(IQPhone::make('009647300000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(IQPhone::make('9647300000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(IQPhone::make('9647900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = IQPhone::make('964 7-300000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('9647300000000');
});

test('is not valid when too short', function () {
    expect(IQPhone::make('730000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(IQPhone::make('79000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(IQPhone::make('9997300000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(IQPhone::make('00300000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(IQPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(IQPhone::make('07300000000')->all())->toEqual(['+9647300000000', '009647300000000', '9647300000000', '07300000000']);
});

test('toArray mirrors all', function () {
    $phone = IQPhone::make('07300000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = IQPhone::make('9647300000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7300000000');
});

test('config exposes the country schema', function () {
    $phone = IQPhone::make('07300000000');
    expect($phone->config('key'))->toEqual('964')
        ->and($phone->config('code'))->toEqual('IQ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(IQPhone::make('964 7-300000000')->number())->toEqual('964 7-300000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = IQPhone::make('07300000000');
    expect($phone->withPlus()->toString())->toEqual('+9647300000000')
        ->and($phone->withoutPlus()->toString())->toEqual('9647300000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(IQPhone::make('07300000000')->toString())->toEqual('+9647300000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '07300000000'], ['phone' => IQPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '730000000'], ['phone' => IQPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(IQPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '730000000'], ['phone' => IQPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '730000000'], ['phone' => IQPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '07300000000'], ['phone' => IQPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '730000000'], ['phone' => IQPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = IQPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(IQPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IQ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(IQPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IQ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(IQPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
