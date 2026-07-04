<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ALPhone;
use MMAE\Phones\Placeholders\ALPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ALPhoneRule;

test('can create a phone object', function () {
    expect(ALPhone::make('0660000000'))->toBeInstanceOf(ALPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ALPhone::make($number)->isValid())->toBeTrue();
})->with(['355660000000', '355670000000', '355680000000', '355690000000']);

test('is valid with the local key', function () {
    expect(ALPhone::make('0660000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ALPhone::make('355660000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ALPhone::make('+355660000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ALPhone::make('00355660000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ALPhone::make('355660000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ALPhone::make('355690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ALPhone::make('355 6-60000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('355660000000');
});

test('is not valid when too short', function () {
    expect(ALPhone::make('66000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ALPhone::make('6900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ALPhone::make('999660000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ALPhone::make('0060000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ALPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ALPhone::make('0660000000')->all())->toEqual(['+355660000000', '00355660000000', '355660000000', '0660000000']);
});

test('toArray mirrors all', function () {
    $phone = ALPhone::make('0660000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ALPhone::make('355660000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('660000000');
});

test('config exposes the country schema', function () {
    $phone = ALPhone::make('0660000000');
    expect($phone->config('key'))->toEqual('355')
        ->and($phone->config('code'))->toEqual('AL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ALPhone::make('355 6-60000000')->number())->toEqual('355 6-60000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ALPhone::make('0660000000');
    expect($phone->withPlus()->toString())->toEqual('+355660000000')
        ->and($phone->withoutPlus()->toString())->toEqual('355660000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ALPhone::make('0660000000')->toString())->toEqual('+355660000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0660000000'], ['phone' => ALPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '66000000'], ['phone' => ALPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ALPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '66000000'], ['phone' => ALPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '66000000'], ['phone' => ALPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0660000000'], ['phone' => ALPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '66000000'], ['phone' => ALPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ALPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ALPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ALPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ALPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
