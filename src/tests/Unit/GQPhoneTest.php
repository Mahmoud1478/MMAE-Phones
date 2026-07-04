<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GQPhone;
use MMAE\Phones\Placeholders\GQPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GQPhoneRule;

test('can create a phone object', function () {
    expect(GQPhone::make('222000000'))->toBeInstanceOf(GQPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GQPhone::make($number)->isValid())->toBeTrue();
})->with(['240222000000', '240550000000']);

test('is valid with the local key', function () {
    expect(GQPhone::make('222000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GQPhone::make('240222000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GQPhone::make('+240222000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GQPhone::make('00240222000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GQPhone::make('240222000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GQPhone::make('240550000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GQPhone::make('240 2-22000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('240222000000');
});

test('is not valid when too short', function () {
    expect(GQPhone::make('22200000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GQPhone::make('5500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GQPhone::make('999222000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GQPhone::make('022000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GQPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GQPhone::make('222000000')->all())->toEqual(['+240222000000', '00240222000000', '240222000000']);
});

test('toArray mirrors all', function () {
    $phone = GQPhone::make('222000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GQPhone::make('240222000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('222000000');
});

test('config exposes the country schema', function () {
    $phone = GQPhone::make('222000000');
    expect($phone->config('key'))->toEqual('240')
        ->and($phone->config('code'))->toEqual('GQ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GQPhone::make('240 2-22000000')->number())->toEqual('240 2-22000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GQPhone::make('222000000');
    expect($phone->withPlus()->toString())->toEqual('+240222000000')
        ->and($phone->withoutPlus()->toString())->toEqual('240222000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GQPhone::make('222000000')->toString())->toEqual('+240222000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '222000000'], ['phone' => GQPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '22200000'], ['phone' => GQPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GQPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '22200000'], ['phone' => GQPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '22200000'], ['phone' => GQPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '222000000'], ['phone' => GQPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '22200000'], ['phone' => GQPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GQPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GQPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GQ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GQPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GQ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GQPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
