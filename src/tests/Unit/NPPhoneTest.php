<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NPPhone;
use MMAE\Phones\Placeholders\NPPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NPPhoneRule;

test('can create a phone object', function () {
    expect(NPPhone::make('09600000000'))->toBeInstanceOf(NPPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NPPhone::make($number)->isValid())->toBeTrue();
})->with(['9779600000000', '9779700000000', '9779800000000']);

test('is valid with the local key', function () {
    expect(NPPhone::make('09600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NPPhone::make('9779600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NPPhone::make('+9779600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NPPhone::make('009779600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NPPhone::make('9779600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NPPhone::make('9779800000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NPPhone::make('977 9-600000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('9779600000000');
});

test('is not valid when too short', function () {
    expect(NPPhone::make('960000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NPPhone::make('98000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NPPhone::make('9999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NPPhone::make('00600000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NPPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NPPhone::make('09600000000')->all())->toEqual(['+9779600000000', '009779600000000', '9779600000000', '09600000000']);
});

test('toArray mirrors all', function () {
    $phone = NPPhone::make('09600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NPPhone::make('9779600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('9600000000');
});

test('config exposes the country schema', function () {
    $phone = NPPhone::make('09600000000');
    expect($phone->config('key'))->toEqual('977')
        ->and($phone->config('code'))->toEqual('NP')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NPPhone::make('977 9-600000000')->number())->toEqual('977 9-600000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NPPhone::make('09600000000');
    expect($phone->withPlus()->toString())->toEqual('+9779600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('9779600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NPPhone::make('09600000000')->toString())->toEqual('+9779600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '09600000000'], ['phone' => NPPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '960000000'], ['phone' => NPPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NPPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '960000000'], ['phone' => NPPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '960000000'], ['phone' => NPPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '09600000000'], ['phone' => NPPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '960000000'], ['phone' => NPPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NPPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NPPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NP');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NPPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NP')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NPPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
