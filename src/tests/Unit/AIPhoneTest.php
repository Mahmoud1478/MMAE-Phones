<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AIPhone;
use MMAE\Phones\Placeholders\AIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AIPhoneRule;

test('can create a phone object', function () {
    expect(AIPhone::make('12640000000'))->toBeInstanceOf(AIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AIPhone::make($number)->isValid())->toBeTrue();
})->with(['12640000000']);

test('is valid with the local key', function () {
    expect(AIPhone::make('12640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AIPhone::make('12640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AIPhone::make('+12640000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AIPhone::make('0012640000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AIPhone::make('12640000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AIPhone::make('12640000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AIPhone::make('1 2-640000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('12640000000');
});

test('is not valid when too short', function () {
    expect(AIPhone::make('264000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AIPhone::make('26400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AIPhone::make('9992640000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AIPhone::make('10640000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AIPhone::make('12640000000')->all())->toEqual(['+12640000000', '0012640000000', '12640000000']);
});

test('toArray mirrors all', function () {
    $phone = AIPhone::make('12640000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AIPhone::make('12640000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2640000000');
});

test('config exposes the country schema', function () {
    $phone = AIPhone::make('12640000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('AI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AIPhone::make('1 2-640000000')->number())->toEqual('1 2-640000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AIPhone::make('12640000000');
    expect($phone->withPlus()->toString())->toEqual('+12640000000')
        ->and($phone->withoutPlus()->toString())->toEqual('12640000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AIPhone::make('12640000000')->toString())->toEqual('+12640000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '12640000000'], ['phone' => AIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '264000000'], ['phone' => AIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '264000000'], ['phone' => AIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '264000000'], ['phone' => AIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '12640000000'], ['phone' => AIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '264000000'], ['phone' => AIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
