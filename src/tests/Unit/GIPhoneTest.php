<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GIPhone;
use MMAE\Phones\Placeholders\GIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GIPhoneRule;

test('can create a phone object', function () {
    expect(GIPhone::make('56000000'))->toBeInstanceOf(GIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GIPhone::make($number)->isValid())->toBeTrue();
})->with(['35056000000', '35057000000', '35058000000']);

test('is valid with the local key', function () {
    expect(GIPhone::make('56000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GIPhone::make('35056000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GIPhone::make('+35056000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GIPhone::make('0035056000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GIPhone::make('35056000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GIPhone::make('35058000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GIPhone::make('350 5-6000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('35056000000');
});

test('is not valid when too short', function () {
    expect(GIPhone::make('5600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GIPhone::make('580000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GIPhone::make('99956000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GIPhone::make('06000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GIPhone::make('56000000')->all())->toEqual(['+35056000000', '0035056000000', '35056000000']);
});

test('toArray mirrors all', function () {
    $phone = GIPhone::make('56000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GIPhone::make('35056000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('56000000');
});

test('config exposes the country schema', function () {
    $phone = GIPhone::make('56000000');
    expect($phone->config('key'))->toEqual('350')
        ->and($phone->config('code'))->toEqual('GI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GIPhone::make('350 5-6000000')->number())->toEqual('350 5-6000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GIPhone::make('56000000');
    expect($phone->withPlus()->toString())->toEqual('+35056000000')
        ->and($phone->withoutPlus()->toString())->toEqual('35056000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GIPhone::make('56000000')->toString())->toEqual('+35056000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '56000000'], ['phone' => GIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5600000'], ['phone' => GIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5600000'], ['phone' => GIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5600000'], ['phone' => GIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '56000000'], ['phone' => GIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5600000'], ['phone' => GIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
