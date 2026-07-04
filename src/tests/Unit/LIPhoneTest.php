<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LIPhone;
use MMAE\Phones\Placeholders\LIPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LIPhoneRule;

test('can create a phone object', function () {
    expect(LIPhone::make('06000000'))->toBeInstanceOf(LIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LIPhone::make($number)->isValid())->toBeTrue();
})->with(['4236000000', '4237000000', '423600000000', '423700000000']);

test('is valid with the local key', function () {
    expect(LIPhone::make('06000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LIPhone::make('4236000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LIPhone::make('+4236000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LIPhone::make('004236000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LIPhone::make('4236000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LIPhone::make('423700000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LIPhone::make('423 6-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('4236000000');
});

test('is not valid when too short', function () {
    expect(LIPhone::make('600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LIPhone::make('7000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LIPhone::make('9996000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LIPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LIPhone::make('06000000')->all())->toEqual(['+4236000000', '004236000000', '4236000000', '06000000']);
});

test('toArray mirrors all', function () {
    $phone = LIPhone::make('06000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LIPhone::make('4236000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6000000');
});

test('config exposes the country schema', function () {
    $phone = LIPhone::make('06000000');
    expect($phone->config('key'))->toEqual('423')
        ->and($phone->config('code'))->toEqual('LI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LIPhone::make('423 6-000000')->number())->toEqual('423 6-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LIPhone::make('06000000');
    expect($phone->withPlus()->toString())->toEqual('+4236000000')
        ->and($phone->withoutPlus()->toString())->toEqual('4236000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LIPhone::make('06000000')->toString())->toEqual('+4236000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '06000000'], ['phone' => LIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => LIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '600000'], ['phone' => LIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '600000'], ['phone' => LIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '06000000'], ['phone' => LIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => LIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
