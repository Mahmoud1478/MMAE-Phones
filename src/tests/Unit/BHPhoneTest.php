<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BHPhone;
use MMAE\Phones\Placeholders\BHPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BHPhoneRule;

test('can create a phone object', function () {
    expect(BHPhone::make('032000000'))->toBeInstanceOf(BHPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BHPhone::make($number)->isValid())->toBeTrue();
})->with(['97332000000', '97333000000', '97334000000', '97335000000', '97336000000', '97337000000', '97338000000', '97339000000']);

test('is valid with the local key', function () {
    expect(BHPhone::make('032000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BHPhone::make('97332000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BHPhone::make('+97332000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BHPhone::make('0097332000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BHPhone::make('97332000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BHPhone::make('97339000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BHPhone::make('973 3-2000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('97332000000');
});

test('is not valid when too short', function () {
    expect(BHPhone::make('3200000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BHPhone::make('390000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BHPhone::make('99932000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BHPhone::make('002000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BHPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BHPhone::make('032000000')->all())->toEqual(['+97332000000', '0097332000000', '97332000000', '032000000']);
});

test('toArray mirrors all', function () {
    $phone = BHPhone::make('032000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BHPhone::make('97332000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('32000000');
});

test('config exposes the country schema', function () {
    $phone = BHPhone::make('032000000');
    expect($phone->config('key'))->toEqual('973')
        ->and($phone->config('code'))->toEqual('BH')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BHPhone::make('973 3-2000000')->number())->toEqual('973 3-2000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BHPhone::make('032000000');
    expect($phone->withPlus()->toString())->toEqual('+97332000000')
        ->and($phone->withoutPlus()->toString())->toEqual('97332000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BHPhone::make('032000000')->toString())->toEqual('+97332000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '032000000'], ['phone' => BHPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3200000'], ['phone' => BHPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BHPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3200000'], ['phone' => BHPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3200000'], ['phone' => BHPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '032000000'], ['phone' => BHPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3200000'], ['phone' => BHPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BHPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BHPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BH');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BHPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BH')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BHPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
