<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\CNPhone;
use MMAE\Phones\Placeholders\CNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\CNPhoneRule;

test('can create a phone object', function () {
    expect(CNPhone::make('013000000000'))->toBeInstanceOf(CNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(CNPhone::make($number)->isValid())->toBeTrue();
})->with(['8613000000000', '8614000000000', '8615000000000', '8616000000000', '8617000000000', '8618000000000', '8619000000000']);

test('is valid with the local key', function () {
    expect(CNPhone::make('013000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(CNPhone::make('8613000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(CNPhone::make('+8613000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(CNPhone::make('008613000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(CNPhone::make('8613000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(CNPhone::make('8619000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = CNPhone::make('86 1-3000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('8613000000000');
});

test('is not valid when too short', function () {
    expect(CNPhone::make('1300000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(CNPhone::make('190000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(CNPhone::make('99913000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(CNPhone::make('003000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(CNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(CNPhone::make('013000000000')->all())->toEqual(['+8613000000000', '008613000000000', '8613000000000', '013000000000']);
});

test('toArray mirrors all', function () {
    $phone = CNPhone::make('013000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = CNPhone::make('8613000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('13000000000');
});

test('config exposes the country schema', function () {
    $phone = CNPhone::make('013000000000');
    expect($phone->config('key'))->toEqual('86')
        ->and($phone->config('code'))->toEqual('CN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(CNPhone::make('86 1-3000000000')->number())->toEqual('86 1-3000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = CNPhone::make('013000000000');
    expect($phone->withPlus()->toString())->toEqual('+8613000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('8613000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(CNPhone::make('013000000000')->toString())->toEqual('+8613000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '013000000000'], ['phone' => CNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '1300000000'], ['phone' => CNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(CNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '1300000000'], ['phone' => CNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '1300000000'], ['phone' => CNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '013000000000'], ['phone' => CNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '1300000000'], ['phone' => CNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = CNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(CNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('CN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(CNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('CN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(CNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
