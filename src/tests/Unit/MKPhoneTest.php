<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MKPhone;
use MMAE\Phones\Placeholders\MKPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MKPhoneRule;

test('can create a phone object', function () {
    expect(MKPhone::make('070000000'))->toBeInstanceOf(MKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MKPhone::make($number)->isValid())->toBeTrue();
})->with(['38970000000', '38971000000', '38972000000', '38973000000', '38974000000', '38975000000', '38976000000', '38977000000', '38978000000', '38979000000']);

test('is valid with the local key', function () {
    expect(MKPhone::make('070000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MKPhone::make('38970000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MKPhone::make('+38970000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MKPhone::make('0038970000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MKPhone::make('38970000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MKPhone::make('38979000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MKPhone::make('389 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('38970000000');
});

test('is not valid when too short', function () {
    expect(MKPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MKPhone::make('790000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MKPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MKPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MKPhone::make('070000000')->all())->toEqual(['+38970000000', '0038970000000', '38970000000', '070000000']);
});

test('toArray mirrors all', function () {
    $phone = MKPhone::make('070000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MKPhone::make('38970000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = MKPhone::make('070000000');
    expect($phone->config('key'))->toEqual('389')
        ->and($phone->config('code'))->toEqual('MK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MKPhone::make('389 7-0000000')->number())->toEqual('389 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MKPhone::make('070000000');
    expect($phone->withPlus()->toString())->toEqual('+38970000000')
        ->and($phone->withoutPlus()->toString())->toEqual('38970000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MKPhone::make('070000000')->toString())->toEqual('+38970000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '070000000'], ['phone' => MKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => MKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '070000000'], ['phone' => MKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
