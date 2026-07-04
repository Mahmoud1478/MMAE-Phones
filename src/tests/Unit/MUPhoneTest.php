<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MUPhone;
use MMAE\Phones\Placeholders\MUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MUPhoneRule;

test('can create a phone object', function () {
    expect(MUPhone::make('50000000'))->toBeInstanceOf(MUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MUPhone::make($number)->isValid())->toBeTrue();
})->with(['23050000000']);

test('is valid with the local key', function () {
    expect(MUPhone::make('50000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MUPhone::make('23050000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MUPhone::make('+23050000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MUPhone::make('0023050000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MUPhone::make('23050000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MUPhone::make('23050000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MUPhone::make('230 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('23050000000');
});

test('is not valid when too short', function () {
    expect(MUPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MUPhone::make('500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MUPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MUPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MUPhone::make('50000000')->all())->toEqual(['+23050000000', '0023050000000', '23050000000']);
});

test('toArray mirrors all', function () {
    $phone = MUPhone::make('50000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MUPhone::make('23050000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = MUPhone::make('50000000');
    expect($phone->config('key'))->toEqual('230')
        ->and($phone->config('code'))->toEqual('MU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MUPhone::make('230 5-0000000')->number())->toEqual('230 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MUPhone::make('50000000');
    expect($phone->withPlus()->toString())->toEqual('+23050000000')
        ->and($phone->withoutPlus()->toString())->toEqual('23050000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MUPhone::make('50000000')->toString())->toEqual('+23050000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => MUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => MUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => MUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => MUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '50000000'], ['phone' => MUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => MUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
