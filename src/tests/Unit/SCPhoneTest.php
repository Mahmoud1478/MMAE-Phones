<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SCPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SCPlaceholder;
use MMAE\Phones\Rules\SCPhoneRule;

test('can create a phone object', function () {
    expect(SCPhone::make('2500000'))->toBeInstanceOf(SCPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SCPhone::make($number)->isValid())->toBeTrue();
})->with(['2482500000', '2482600000', '2482700000', '2482800000']);

test('is valid with the local key', function () {
    expect(SCPhone::make('2500000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SCPhone::make('2482500000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SCPhone::make('+2482500000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SCPhone::make('002482500000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SCPhone::make('2482500000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SCPhone::make('2482800000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SCPhone::make('248 2-500000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2482500000');
});

test('is not valid when too short', function () {
    expect(SCPhone::make('250000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SCPhone::make('28000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SCPhone::make('9992500000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SCPhone::make('0500000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SCPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SCPhone::make('2500000')->all())->toEqual(['+2482500000', '002482500000', '2482500000']);
});

test('toArray mirrors all', function () {
    $phone = SCPhone::make('2500000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SCPhone::make('2482500000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('2500000');
});

test('config exposes the country schema', function () {
    $phone = SCPhone::make('2500000');
    expect($phone->config('key'))->toEqual('248')
        ->and($phone->config('code'))->toEqual('SC')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SCPhone::make('248 2-500000')->number())->toEqual('248 2-500000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SCPhone::make('2500000');
    expect($phone->withPlus()->toString())->toEqual('+2482500000')
        ->and($phone->withoutPlus()->toString())->toEqual('2482500000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SCPhone::make('2500000')->toString())->toEqual('+2482500000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '2500000'], ['phone' => SCPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '250000'], ['phone' => SCPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SCPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '250000'], ['phone' => SCPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '250000'], ['phone' => SCPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '2500000'], ['phone' => SCPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '250000'], ['phone' => SCPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SCPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SCPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SC');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SCPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SC')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SCPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
