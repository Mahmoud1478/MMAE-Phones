<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MOPhone;
use MMAE\Phones\Placeholders\MOPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MOPhoneRule;

test('can create a phone object', function () {
    expect(MOPhone::make('60000000'))->toBeInstanceOf(MOPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MOPhone::make($number)->isValid())->toBeTrue();
})->with(['85360000000']);

test('is valid with the local key', function () {
    expect(MOPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MOPhone::make('85360000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MOPhone::make('+85360000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MOPhone::make('0085360000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MOPhone::make('85360000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MOPhone::make('85360000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MOPhone::make('853 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('85360000000');
});

test('is not valid when too short', function () {
    expect(MOPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MOPhone::make('600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MOPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MOPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MOPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MOPhone::make('60000000')->all())->toEqual(['+85360000000', '0085360000000', '85360000000']);
});

test('toArray mirrors all', function () {
    $phone = MOPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MOPhone::make('85360000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = MOPhone::make('60000000');
    expect($phone->config('key'))->toEqual('853')
        ->and($phone->config('code'))->toEqual('MO')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MOPhone::make('853 6-0000000')->number())->toEqual('853 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MOPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+85360000000')
        ->and($phone->withoutPlus()->toString())->toEqual('85360000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MOPhone::make('60000000')->toString())->toEqual('+85360000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => MOPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MOPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MOPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MOPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => MOPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => MOPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MOPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MOPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MOPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MO');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MOPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MO')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MOPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
