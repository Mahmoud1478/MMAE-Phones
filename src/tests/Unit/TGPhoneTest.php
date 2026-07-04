<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TGPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TGPlaceholder;
use MMAE\Phones\Rules\TGPhoneRule;

test('can create a phone object', function () {
    expect(TGPhone::make('70000000'))->toBeInstanceOf(TGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TGPhone::make($number)->isValid())->toBeTrue();
})->with(['22870000000', '22890000000']);

test('is valid with the local key', function () {
    expect(TGPhone::make('70000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TGPhone::make('22870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TGPhone::make('+22870000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TGPhone::make('0022870000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TGPhone::make('22870000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TGPhone::make('22890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TGPhone::make('228 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('22870000000');
});

test('is not valid when too short', function () {
    expect(TGPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TGPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TGPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TGPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TGPhone::make('70000000')->all())->toEqual(['+22870000000', '0022870000000', '22870000000']);
});

test('toArray mirrors all', function () {
    $phone = TGPhone::make('70000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TGPhone::make('22870000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = TGPhone::make('70000000');
    expect($phone->config('key'))->toEqual('228')
        ->and($phone->config('code'))->toEqual('TG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TGPhone::make('228 7-0000000')->number())->toEqual('228 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TGPhone::make('70000000');
    expect($phone->withPlus()->toString())->toEqual('+22870000000')
        ->and($phone->withoutPlus()->toString())->toEqual('22870000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TGPhone::make('70000000')->toString())->toEqual('+22870000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => TGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => TGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => TGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => TGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '70000000'], ['phone' => TGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => TGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
