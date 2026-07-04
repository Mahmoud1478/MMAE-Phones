<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KGPhone;
use MMAE\Phones\Placeholders\KGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KGPhoneRule;

test('can create a phone object', function () {
    expect(KGPhone::make('0500000000'))->toBeInstanceOf(KGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KGPhone::make($number)->isValid())->toBeTrue();
})->with(['996500000000', '996511000000', '996522000000', '996533000000', '996544000000', '996555000000', '996566000000', '996577000000', '996588000000', '996599000000', '996700000000', '996711000000', '996722000000', '996733000000', '996744000000', '996755000000', '996766000000', '996777000000', '996788000000', '996799000000']);

test('is valid with the local key', function () {
    expect(KGPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KGPhone::make('996500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KGPhone::make('+996500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KGPhone::make('00996500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KGPhone::make('996500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KGPhone::make('996799000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KGPhone::make('996 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('996500000000');
});

test('is not valid when too short', function () {
    expect(KGPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KGPhone::make('7990000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KGPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KGPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KGPhone::make('0500000000')->all())->toEqual(['+996500000000', '00996500000000', '996500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = KGPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KGPhone::make('996500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = KGPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('996')
        ->and($phone->config('code'))->toEqual('KG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KGPhone::make('996 5-00000000')->number())->toEqual('996 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KGPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+996500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('996500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KGPhone::make('0500000000')->toString())->toEqual('+996500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => KGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => KGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => KGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => KGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => KGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => KGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
