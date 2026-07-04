<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SGPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SGPlaceholder;
use MMAE\Phones\Rules\SGPhoneRule;

test('can create a phone object', function () {
    expect(SGPhone::make('80000000'))->toBeInstanceOf(SGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SGPhone::make($number)->isValid())->toBeTrue();
})->with(['6580000000', '6590000000']);

test('is valid with the local key', function () {
    expect(SGPhone::make('80000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SGPhone::make('6580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SGPhone::make('+6580000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SGPhone::make('006580000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SGPhone::make('6580000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SGPhone::make('6590000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SGPhone::make('65 8-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('6580000000');
});

test('is not valid when too short', function () {
    expect(SGPhone::make('8000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SGPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SGPhone::make('99980000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SGPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SGPhone::make('80000000')->all())->toEqual(['+6580000000', '006580000000', '6580000000']);
});

test('toArray mirrors all', function () {
    $phone = SGPhone::make('80000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SGPhone::make('6580000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('80000000');
});

test('config exposes the country schema', function () {
    $phone = SGPhone::make('80000000');
    expect($phone->config('key'))->toEqual('65')
        ->and($phone->config('code'))->toEqual('SG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SGPhone::make('65 8-0000000')->number())->toEqual('65 8-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SGPhone::make('80000000');
    expect($phone->withPlus()->toString())->toEqual('+6580000000')
        ->and($phone->withoutPlus()->toString())->toEqual('6580000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SGPhone::make('80000000')->toString())->toEqual('+6580000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => SGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => SGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '8000000'], ['phone' => SGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '8000000'], ['phone' => SGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '80000000'], ['phone' => SGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => SGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
