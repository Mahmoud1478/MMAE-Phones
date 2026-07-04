<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KPPhone;
use MMAE\Phones\Placeholders\KPPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KPPhoneRule;

test('can create a phone object', function () {
    expect(KPPhone::make('01900000000'))->toBeInstanceOf(KPPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KPPhone::make($number)->isValid())->toBeTrue();
})->with(['8501900000000']);

test('is valid with the local key', function () {
    expect(KPPhone::make('01900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KPPhone::make('8501900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KPPhone::make('+8501900000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KPPhone::make('008501900000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KPPhone::make('8501900000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KPPhone::make('8501900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KPPhone::make('850 1-900000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('8501900000000');
});

test('is not valid when too short', function () {
    expect(KPPhone::make('190000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KPPhone::make('19000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KPPhone::make('9991900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KPPhone::make('00900000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KPPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KPPhone::make('01900000000')->all())->toEqual(['+8501900000000', '008501900000000', '8501900000000', '01900000000']);
});

test('toArray mirrors all', function () {
    $phone = KPPhone::make('01900000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KPPhone::make('8501900000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('1900000000');
});

test('config exposes the country schema', function () {
    $phone = KPPhone::make('01900000000');
    expect($phone->config('key'))->toEqual('850')
        ->and($phone->config('code'))->toEqual('KP')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KPPhone::make('850 1-900000000')->number())->toEqual('850 1-900000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KPPhone::make('01900000000');
    expect($phone->withPlus()->toString())->toEqual('+8501900000000')
        ->and($phone->withoutPlus()->toString())->toEqual('8501900000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KPPhone::make('01900000000')->toString())->toEqual('+8501900000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01900000000'], ['phone' => KPPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '190000000'], ['phone' => KPPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KPPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '190000000'], ['phone' => KPPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '190000000'], ['phone' => KPPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '01900000000'], ['phone' => KPPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '190000000'], ['phone' => KPPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KPPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KPPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KP');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KPPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KP')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KPPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
