<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PGPhone;
use MMAE\Phones\Placeholders\PGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\PGPhoneRule;

test('can create a phone object', function () {
    expect(PGPhone::make('70000000'))->toBeInstanceOf(PGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PGPhone::make($number)->isValid())->toBeTrue();
})->with(['67570000000', '67580000000']);

test('is valid with the local key', function () {
    expect(PGPhone::make('70000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PGPhone::make('67570000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PGPhone::make('+67570000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PGPhone::make('0067570000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PGPhone::make('67570000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PGPhone::make('67580000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PGPhone::make('675 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('67570000000');
});

test('is not valid when too short', function () {
    expect(PGPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PGPhone::make('800000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PGPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PGPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PGPhone::make('70000000')->all())->toEqual(['+67570000000', '0067570000000', '67570000000']);
});

test('toArray mirrors all', function () {
    $phone = PGPhone::make('70000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PGPhone::make('67570000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = PGPhone::make('70000000');
    expect($phone->config('key'))->toEqual('675')
        ->and($phone->config('code'))->toEqual('PG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PGPhone::make('675 7-0000000')->number())->toEqual('675 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PGPhone::make('70000000');
    expect($phone->withPlus()->toString())->toEqual('+67570000000')
        ->and($phone->withoutPlus()->toString())->toEqual('67570000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PGPhone::make('70000000')->toString())->toEqual('+67570000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => PGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => PGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => PGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => PGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '70000000'], ['phone' => PGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => PGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
