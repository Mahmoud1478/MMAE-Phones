<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MTPhone;
use MMAE\Phones\Placeholders\MTPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MTPhoneRule;

test('can create a phone object', function () {
    expect(MTPhone::make('70000000'))->toBeInstanceOf(MTPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MTPhone::make($number)->isValid())->toBeTrue();
})->with(['35670000000', '35690000000']);

test('is valid with the local key', function () {
    expect(MTPhone::make('70000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MTPhone::make('35670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MTPhone::make('+35670000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MTPhone::make('0035670000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MTPhone::make('35670000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MTPhone::make('35690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MTPhone::make('356 7-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('35670000000');
});

test('is not valid when too short', function () {
    expect(MTPhone::make('7000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MTPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MTPhone::make('99970000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MTPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MTPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MTPhone::make('70000000')->all())->toEqual(['+35670000000', '0035670000000', '35670000000']);
});

test('toArray mirrors all', function () {
    $phone = MTPhone::make('70000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MTPhone::make('35670000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('70000000');
});

test('config exposes the country schema', function () {
    $phone = MTPhone::make('70000000');
    expect($phone->config('key'))->toEqual('356')
        ->and($phone->config('code'))->toEqual('MT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MTPhone::make('356 7-0000000')->number())->toEqual('356 7-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MTPhone::make('70000000');
    expect($phone->withPlus()->toString())->toEqual('+35670000000')
        ->and($phone->withoutPlus()->toString())->toEqual('35670000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MTPhone::make('70000000')->toString())->toEqual('+35670000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => MTPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MTPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MTPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MTPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7000000'], ['phone' => MTPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '70000000'], ['phone' => MTPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7000000'], ['phone' => MTPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MTPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MTPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MTPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MTPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
