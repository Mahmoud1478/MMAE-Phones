<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\JMPhone;
use MMAE\Phones\Placeholders\JMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\JMPhoneRule;

test('can create a phone object', function () {
    expect(JMPhone::make('16580000000'))->toBeInstanceOf(JMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(JMPhone::make($number)->isValid())->toBeTrue();
})->with(['16580000000', '18760000000']);

test('is valid with the local key', function () {
    expect(JMPhone::make('16580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(JMPhone::make('16580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(JMPhone::make('+16580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(JMPhone::make('0016580000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(JMPhone::make('16580000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(JMPhone::make('18760000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = JMPhone::make('1 6-580000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16580000000');
});

test('is not valid when too short', function () {
    expect(JMPhone::make('658000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(JMPhone::make('87600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(JMPhone::make('9996580000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(JMPhone::make('10580000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(JMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(JMPhone::make('16580000000')->all())->toEqual(['+16580000000', '0016580000000', '16580000000']);
});

test('toArray mirrors all', function () {
    $phone = JMPhone::make('16580000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = JMPhone::make('16580000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6580000000');
});

test('config exposes the country schema', function () {
    $phone = JMPhone::make('16580000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('JM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(JMPhone::make('1 6-580000000')->number())->toEqual('1 6-580000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = JMPhone::make('16580000000');
    expect($phone->withPlus()->toString())->toEqual('+16580000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16580000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(JMPhone::make('16580000000')->toString())->toEqual('+16580000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16580000000'], ['phone' => JMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '658000000'], ['phone' => JMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(JMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '658000000'], ['phone' => JMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '658000000'], ['phone' => JMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16580000000'], ['phone' => JMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '658000000'], ['phone' => JMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = JMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(JMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('JM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(JMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('JM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(JMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
