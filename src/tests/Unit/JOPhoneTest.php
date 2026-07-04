<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\JOPhone;
use MMAE\Phones\Placeholders\JOPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\JOPhoneRule;

test('can create a phone object', function () {
    expect(JOPhone::make('0770000000'))->toBeInstanceOf(JOPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(JOPhone::make($number)->isValid())->toBeTrue();
})->with(['962770000000', '962780000000', '962790000000']);

test('is valid with the local key', function () {
    expect(JOPhone::make('0770000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(JOPhone::make('962770000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(JOPhone::make('+962770000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(JOPhone::make('00962770000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(JOPhone::make('962770000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(JOPhone::make('962790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = JOPhone::make('962 7-70000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('962770000000');
});

test('is not valid when too short', function () {
    expect(JOPhone::make('77000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(JOPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(JOPhone::make('999770000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(JOPhone::make('0070000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(JOPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(JOPhone::make('0770000000')->all())->toEqual(['+962770000000', '00962770000000', '962770000000', '0770000000']);
});

test('toArray mirrors all', function () {
    $phone = JOPhone::make('0770000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = JOPhone::make('962770000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('770000000');
});

test('config exposes the country schema', function () {
    $phone = JOPhone::make('0770000000');
    expect($phone->config('key'))->toEqual('962')
        ->and($phone->config('code'))->toEqual('JO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(JOPhone::make('962 7-70000000')->number())->toEqual('962 7-70000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = JOPhone::make('0770000000');
    expect($phone->withPlus()->toString())->toEqual('+962770000000')
        ->and($phone->withoutPlus()->toString())->toEqual('962770000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(JOPhone::make('0770000000')->toString())->toEqual('+962770000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0770000000'], ['phone' => JOPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '77000000'], ['phone' => JOPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(JOPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '77000000'], ['phone' => JOPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '77000000'], ['phone' => JOPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0770000000'], ['phone' => JOPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '77000000'], ['phone' => JOPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = JOPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(JOPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('JO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(JOPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('JO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(JOPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
