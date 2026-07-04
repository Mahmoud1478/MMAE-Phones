<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\IEPhone;
use MMAE\Phones\Placeholders\IEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\IEPhoneRule;

test('can create a phone object', function () {
    expect(IEPhone::make('0830000000'))->toBeInstanceOf(IEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(IEPhone::make($number)->isValid())->toBeTrue();
})->with(['353830000000', '353840000000', '353850000000', '353860000000', '353870000000', '353880000000', '353890000000']);

test('is valid with the local key', function () {
    expect(IEPhone::make('0830000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(IEPhone::make('353830000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(IEPhone::make('+353830000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(IEPhone::make('00353830000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(IEPhone::make('353830000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(IEPhone::make('353890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = IEPhone::make('353 8-30000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('353830000000');
});

test('is not valid when too short', function () {
    expect(IEPhone::make('83000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(IEPhone::make('8900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(IEPhone::make('999830000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(IEPhone::make('0030000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(IEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(IEPhone::make('0830000000')->all())->toEqual(['+353830000000', '00353830000000', '353830000000', '0830000000']);
});

test('toArray mirrors all', function () {
    $phone = IEPhone::make('0830000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = IEPhone::make('353830000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('830000000');
});

test('config exposes the country schema', function () {
    $phone = IEPhone::make('0830000000');
    expect($phone->config('key'))->toEqual('353')
        ->and($phone->config('code'))->toEqual('IE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(IEPhone::make('353 8-30000000')->number())->toEqual('353 8-30000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = IEPhone::make('0830000000');
    expect($phone->withPlus()->toString())->toEqual('+353830000000')
        ->and($phone->withoutPlus()->toString())->toEqual('353830000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(IEPhone::make('0830000000')->toString())->toEqual('+353830000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0830000000'], ['phone' => IEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '83000000'], ['phone' => IEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(IEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '83000000'], ['phone' => IEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '83000000'], ['phone' => IEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0830000000'], ['phone' => IEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '83000000'], ['phone' => IEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = IEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(IEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(IEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(IEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
