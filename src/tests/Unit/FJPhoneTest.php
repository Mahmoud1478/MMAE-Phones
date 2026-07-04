<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\FJPhone;
use MMAE\Phones\Placeholders\FJPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\FJPhoneRule;

test('can create a phone object', function () {
    expect(FJPhone::make('7000000'))->toBeInstanceOf(FJPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(FJPhone::make($number)->isValid())->toBeTrue();
})->with(['6797000000', '6798000000', '6799000000']);

test('is valid with the local key', function () {
    expect(FJPhone::make('7000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(FJPhone::make('6797000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(FJPhone::make('+6797000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(FJPhone::make('006797000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(FJPhone::make('6797000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(FJPhone::make('6799000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = FJPhone::make('679 7-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6797000000');
});

test('is not valid when too short', function () {
    expect(FJPhone::make('700000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(FJPhone::make('90000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(FJPhone::make('9997000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(FJPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(FJPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(FJPhone::make('7000000')->all())->toEqual(['+6797000000', '006797000000', '6797000000']);
});

test('toArray mirrors all', function () {
    $phone = FJPhone::make('7000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = FJPhone::make('6797000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000');
});

test('config exposes the country schema', function () {
    $phone = FJPhone::make('7000000');
    expect($phone->config('key'))->toEqual('679')
        ->and($phone->config('code'))->toEqual('FJ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(FJPhone::make('679 7-000000')->number())->toEqual('679 7-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = FJPhone::make('7000000');
    expect($phone->withPlus()->toString())->toEqual('+6797000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6797000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(FJPhone::make('7000000')->toString())->toEqual('+6797000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => FJPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => FJPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(FJPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000'], ['phone' => FJPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000'], ['phone' => FJPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '7000000'], ['phone' => FJPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000'], ['phone' => FJPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = FJPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(FJPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('FJ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(FJPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('FJ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(FJPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
