<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MLPhone;
use MMAE\Phones\Placeholders\MLPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MLPhoneRule;

test('can create a phone object', function () {
    expect(MLPhone::make('60000000'))->toBeInstanceOf(MLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MLPhone::make($number)->isValid())->toBeTrue();
})->with(['22360000000', '22370000000', '22390000000']);

test('is valid with the local key', function () {
    expect(MLPhone::make('60000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MLPhone::make('22360000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MLPhone::make('+22360000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MLPhone::make('0022360000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MLPhone::make('22360000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MLPhone::make('22390000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MLPhone::make('223 6-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('22360000000');
});

test('is not valid when too short', function () {
    expect(MLPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MLPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MLPhone::make('99960000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MLPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MLPhone::make('60000000')->all())->toEqual(['+22360000000', '0022360000000', '22360000000']);
});

test('toArray mirrors all', function () {
    $phone = MLPhone::make('60000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MLPhone::make('22360000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('60000000');
});

test('config exposes the country schema', function () {
    $phone = MLPhone::make('60000000');
    expect($phone->config('key'))->toEqual('223')
        ->and($phone->config('code'))->toEqual('ML')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MLPhone::make('223 6-0000000')->number())->toEqual('223 6-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MLPhone::make('60000000');
    expect($phone->withPlus()->toString())->toEqual('+22360000000')
        ->and($phone->withoutPlus()->toString())->toEqual('22360000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MLPhone::make('60000000')->toString())->toEqual('+22360000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => MLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '6000000'], ['phone' => MLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '60000000'], ['phone' => MLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => MLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ML');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ML')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
