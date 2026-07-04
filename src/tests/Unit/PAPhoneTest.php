<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\PAPhone;
use MMAE\Phones\Placeholders\PAPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\PAPhoneRule;

test('can create a phone object', function () {
    expect(PAPhone::make('60000000'))->toBeInstanceOf(PAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(PAPhone::make($number)->isValid())->toBeTrue();
})->with(['50760000000']);

test('is valid with the local key', function () {
    expect(PAPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(PAPhone::make('50760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(PAPhone::make('+50760000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(PAPhone::make('0050760000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(PAPhone::make('50760000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(PAPhone::make('50760000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = PAPhone::make('507 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('50760000000');
});

test('is not valid when too short', function () {
    expect(PAPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(PAPhone::make('600000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(PAPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(PAPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(PAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(PAPhone::make('60000000')->all())->toEqual(['+50760000000', '0050760000000', '50760000000']);
});

test('toArray mirrors all', function () {
    $phone = PAPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = PAPhone::make('50760000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = PAPhone::make('60000000');
    expect($phone->config('key'))->toEqual('507')
        ->and($phone->config('code'))->toEqual('PA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(PAPhone::make('507 6-0000000')->number())->toEqual('507 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = PAPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+50760000000')
        ->and($phone->withoutPlus()->toString())->toEqual('50760000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(PAPhone::make('60000000')->toString())->toEqual('+50760000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => PAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => PAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(PAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => PAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => PAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => PAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => PAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = PAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(PAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('PA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(PAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('PA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(PAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
