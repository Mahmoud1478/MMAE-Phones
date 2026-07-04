<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SLPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SLPlaceholder;
use MMAE\Phones\Rules\SLPhoneRule;

test('can create a phone object', function () {
    expect(SLPhone::make('020000000'))->toBeInstanceOf(SLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SLPhone::make($number)->isValid())->toBeTrue();
})->with(['23220000000', '23230000000', '23240000000', '23250000000', '23260000000', '23270000000', '23280000000', '23290000000']);

test('is valid with the local key', function () {
    expect(SLPhone::make('020000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SLPhone::make('23220000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SLPhone::make('+23220000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SLPhone::make('0023220000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SLPhone::make('23220000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SLPhone::make('23290000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SLPhone::make('232 2-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('23220000000');
});

test('is not valid when too short', function () {
    expect(SLPhone::make('2000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SLPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SLPhone::make('99920000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SLPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SLPhone::make('020000000')->all())->toEqual(['+23220000000', '0023220000000', '23220000000', '020000000']);
});

test('toArray mirrors all', function () {
    $phone = SLPhone::make('020000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SLPhone::make('23220000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('20000000');
});

test('config exposes the country schema', function () {
    $phone = SLPhone::make('020000000');
    expect($phone->config('key'))->toEqual('232')
        ->and($phone->config('code'))->toEqual('SL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SLPhone::make('232 2-0000000')->number())->toEqual('232 2-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SLPhone::make('020000000');
    expect($phone->withPlus()->toString())->toEqual('+23220000000')
        ->and($phone->withoutPlus()->toString())->toEqual('23220000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SLPhone::make('020000000')->toString())->toEqual('+23220000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '020000000'], ['phone' => SLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => SLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '2000000'], ['phone' => SLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '2000000'], ['phone' => SLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '020000000'], ['phone' => SLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => SLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
