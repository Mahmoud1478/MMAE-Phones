<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MGPhone;
use MMAE\Phones\Placeholders\MGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MGPhoneRule;

test('can create a phone object', function () {
    expect(MGPhone::make('0320000000'))->toBeInstanceOf(MGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MGPhone::make($number)->isValid())->toBeTrue();
})->with(['261320000000', '261330000000', '261340000000']);

test('is valid with the local key', function () {
    expect(MGPhone::make('0320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MGPhone::make('261320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MGPhone::make('+261320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MGPhone::make('00261320000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MGPhone::make('261320000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MGPhone::make('261340000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MGPhone::make('261 3-20000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('261320000000');
});

test('is not valid when too short', function () {
    expect(MGPhone::make('32000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MGPhone::make('3400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MGPhone::make('999320000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MGPhone::make('0020000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MGPhone::make('0320000000')->all())->toEqual(['+261320000000', '00261320000000', '261320000000', '0320000000']);
});

test('toArray mirrors all', function () {
    $phone = MGPhone::make('0320000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MGPhone::make('261320000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('320000000');
});

test('config exposes the country schema', function () {
    $phone = MGPhone::make('0320000000');
    expect($phone->config('key'))->toEqual('261')
        ->and($phone->config('code'))->toEqual('MG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MGPhone::make('261 3-20000000')->number())->toEqual('261 3-20000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MGPhone::make('0320000000');
    expect($phone->withPlus()->toString())->toEqual('+261320000000')
        ->and($phone->withoutPlus()->toString())->toEqual('261320000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MGPhone::make('0320000000')->toString())->toEqual('+261320000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0320000000'], ['phone' => MGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '32000000'], ['phone' => MGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '32000000'], ['phone' => MGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '32000000'], ['phone' => MGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0320000000'], ['phone' => MGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '32000000'], ['phone' => MGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
