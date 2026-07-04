<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ZMPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\ZMPlaceholder;
use MMAE\Phones\Rules\ZMPhoneRule;

test('can create a phone object', function () {
    expect(ZMPhone::make('0950000000'))->toBeInstanceOf(ZMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ZMPhone::make($number)->isValid())->toBeTrue();
})->with(['260950000000', '260960000000', '260970000000']);

test('is valid with the local key', function () {
    expect(ZMPhone::make('0950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ZMPhone::make('260950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ZMPhone::make('+260950000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ZMPhone::make('00260950000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ZMPhone::make('260950000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ZMPhone::make('260970000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ZMPhone::make('260 9-50000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('260950000000');
});

test('is not valid when too short', function () {
    expect(ZMPhone::make('95000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ZMPhone::make('9700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ZMPhone::make('999950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ZMPhone::make('0050000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ZMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ZMPhone::make('0950000000')->all())->toEqual(['+260950000000', '00260950000000', '260950000000', '0950000000']);
});

test('toArray mirrors all', function () {
    $phone = ZMPhone::make('0950000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ZMPhone::make('260950000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('950000000');
});

test('config exposes the country schema', function () {
    $phone = ZMPhone::make('0950000000');
    expect($phone->config('key'))->toEqual('260')
        ->and($phone->config('code'))->toEqual('ZM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ZMPhone::make('260 9-50000000')->number())->toEqual('260 9-50000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ZMPhone::make('0950000000');
    expect($phone->withPlus()->toString())->toEqual('+260950000000')
        ->and($phone->withoutPlus()->toString())->toEqual('260950000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ZMPhone::make('0950000000')->toString())->toEqual('+260950000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0950000000'], ['phone' => ZMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '95000000'], ['phone' => ZMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ZMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '95000000'], ['phone' => ZMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '95000000'], ['phone' => ZMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0950000000'], ['phone' => ZMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '95000000'], ['phone' => ZMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ZMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ZMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ZM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ZMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ZM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ZMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
