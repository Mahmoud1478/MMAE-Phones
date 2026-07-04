<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TLPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TLPlaceholder;
use MMAE\Phones\Rules\TLPhoneRule;

test('can create a phone object', function () {
    expect(TLPhone::make('72000000'))->toBeInstanceOf(TLPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TLPhone::make($number)->isValid())->toBeTrue();
})->with(['67072000000', '67073000000', '67074000000', '67075000000', '67076000000', '67077000000', '67078000000']);

test('is valid with the local key', function () {
    expect(TLPhone::make('72000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TLPhone::make('67072000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TLPhone::make('+67072000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TLPhone::make('0067072000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TLPhone::make('67072000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TLPhone::make('67078000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TLPhone::make('670 7-2000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('67072000000');
});

test('is not valid when too short', function () {
    expect(TLPhone::make('7200000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TLPhone::make('780000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TLPhone::make('99972000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TLPhone::make('02000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TLPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TLPhone::make('72000000')->all())->toEqual(['+67072000000', '0067072000000', '67072000000']);
});

test('toArray mirrors all', function () {
    $phone = TLPhone::make('72000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TLPhone::make('67072000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('72000000');
});

test('config exposes the country schema', function () {
    $phone = TLPhone::make('72000000');
    expect($phone->config('key'))->toEqual('670')
        ->and($phone->config('code'))->toEqual('TL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TLPhone::make('670 7-2000000')->number())->toEqual('670 7-2000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TLPhone::make('72000000');
    expect($phone->withPlus()->toString())->toEqual('+67072000000')
        ->and($phone->withoutPlus()->toString())->toEqual('67072000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TLPhone::make('72000000')->toString())->toEqual('+67072000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '72000000'], ['phone' => TLPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7200000'], ['phone' => TLPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TLPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7200000'], ['phone' => TLPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7200000'], ['phone' => TLPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '72000000'], ['phone' => TLPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7200000'], ['phone' => TLPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TLPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TLPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TLPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TLPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
