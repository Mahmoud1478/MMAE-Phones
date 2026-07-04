<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CFPhone;
use MMAE\Phones\Placeholders\CFPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CFPhoneRule;

test('can create a phone object', function () {
    expect(CFPhone::make('70000000'))->toBeInstanceOf(CFPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CFPhone::make($number)->isValid())->toBeTrue();
})->with(['23670000000', '23671000000', '23672000000', '23673000000', '23674000000', '23675000000', '23676000000', '23677000000']);

test('is valid with the local key', function () {
    expect(CFPhone::make('70000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CFPhone::make('23670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CFPhone::make('+23670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CFPhone::make('0023670000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CFPhone::make('23670000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CFPhone::make('23677000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CFPhone::make('236 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('23670000000');
});

test('is not valid when too short', function () {
    expect(CFPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CFPhone::make('770000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CFPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CFPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CFPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CFPhone::make('70000000')->all())->toEqual(['+23670000000', '0023670000000', '23670000000']);
});

test('toArray mirrors all', function () {
    $phone = CFPhone::make('70000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CFPhone::make('23670000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = CFPhone::make('70000000');
    expect($phone->config('key'))->toEqual('236')
        ->and($phone->config('code'))->toEqual('CF')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CFPhone::make('236 7-0000000')->number())->toEqual('236 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CFPhone::make('70000000');
    expect($phone->withPlus()->toString())->toEqual('+23670000000')
        ->and($phone->withoutPlus()->toString())->toEqual('23670000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CFPhone::make('70000000')->toString())->toEqual('+23670000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => CFPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => CFPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CFPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => CFPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => CFPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '70000000'], ['phone' => CFPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => CFPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CFPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CFPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CF');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CFPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CF')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CFPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
