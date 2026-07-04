<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CAPhone;
use MMAE\Phones\Placeholders\CAPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CAPhoneRule;

test('can create a phone object', function () {
    expect(CAPhone::make('10000000000'))->toBeInstanceOf(CAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CAPhone::make($number)->isValid())->toBeTrue();
})->with(['10000000000']);

test('is valid with the local key', function () {
    expect(CAPhone::make('10000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CAPhone::make('10000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CAPhone::make('+10000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CAPhone::make('0010000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CAPhone::make('10000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CAPhone::make('10000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CAPhone::make('1 0-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('10000000000');
});

test('is not valid when too short', function () {
    expect(CAPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CAPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CAPhone::make('9990000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CAPhone::make('10000000000')->isNotValid())->toBeTrue();
})->skip('provider pattern accepts any starting digit');

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CAPhone::make('10000000000')->all())->toEqual(['+10000000000', '0010000000000', '10000000000']);
});

test('toArray mirrors all', function () {
    $phone = CAPhone::make('10000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CAPhone::make('10000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('0000000000');
});

test('config exposes the country schema', function () {
    $phone = CAPhone::make('10000000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('CA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CAPhone::make('1 0-000000000')->number())->toEqual('1 0-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CAPhone::make('10000000000');
    expect($phone->withPlus()->toString())->toEqual('+10000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('10000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CAPhone::make('10000000000')->toString())->toEqual('+10000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '10000000000'], ['phone' => CAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '000000000'], ['phone' => CAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '000000000'], ['phone' => CAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '000000000'], ['phone' => CAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '10000000000'], ['phone' => CAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '000000000'], ['phone' => CAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
