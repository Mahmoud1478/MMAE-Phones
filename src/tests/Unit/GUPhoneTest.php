<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GUPhone;
use MMAE\Phones\Placeholders\GUPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GUPhoneRule;

test('can create a phone object', function () {
    expect(GUPhone::make('16710000000'))->toBeInstanceOf(GUPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GUPhone::make($number)->isValid())->toBeTrue();
})->with(['16710000000']);

test('is valid with the local key', function () {
    expect(GUPhone::make('16710000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GUPhone::make('16710000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GUPhone::make('+16710000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GUPhone::make('0016710000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GUPhone::make('16710000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GUPhone::make('16710000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GUPhone::make('1 6-710000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('16710000000');
});

test('is not valid when too short', function () {
    expect(GUPhone::make('671000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GUPhone::make('67100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GUPhone::make('9996710000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GUPhone::make('10710000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GUPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GUPhone::make('16710000000')->all())->toEqual(['+16710000000', '0016710000000', '16710000000']);
});

test('toArray mirrors all', function () {
    $phone = GUPhone::make('16710000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GUPhone::make('16710000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6710000000');
});

test('config exposes the country schema', function () {
    $phone = GUPhone::make('16710000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('GU')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GUPhone::make('1 6-710000000')->number())->toEqual('1 6-710000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GUPhone::make('16710000000');
    expect($phone->withPlus()->toString())->toEqual('+16710000000')
        ->and($phone->withoutPlus()->toString())->toEqual('16710000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GUPhone::make('16710000000')->toString())->toEqual('+16710000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '16710000000'], ['phone' => GUPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '671000000'], ['phone' => GUPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GUPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '671000000'], ['phone' => GUPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '671000000'], ['phone' => GUPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '16710000000'], ['phone' => GUPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '671000000'], ['phone' => GUPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GUPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GUPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GU');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GUPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GU')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GUPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
