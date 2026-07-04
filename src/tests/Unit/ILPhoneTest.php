<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ILPhone;
use MMAE\Phones\Placeholders\ILPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ILPhoneRule;

test('can create a phone object', function () {
    expect(ILPhone::make('0500000000'))->toBeInstanceOf(ILPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ILPhone::make($number)->isValid())->toBeTrue();
})->with(['972500000000', '972510000000', '972520000000', '972530000000', '972540000000', '972550000000', '972560000000', '972570000000', '972580000000', '972590000000']);

test('is valid with the local key', function () {
    expect(ILPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ILPhone::make('972500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ILPhone::make('+972500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ILPhone::make('00972500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ILPhone::make('972500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ILPhone::make('972590000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ILPhone::make('972 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('972500000000');
});

test('is not valid when too short', function () {
    expect(ILPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ILPhone::make('5900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ILPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ILPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ILPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ILPhone::make('0500000000')->all())->toEqual(['+972500000000', '00972500000000', '972500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = ILPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ILPhone::make('972500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = ILPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('972')
        ->and($phone->config('code'))->toEqual('IL')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ILPhone::make('972 5-00000000')->number())->toEqual('972 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ILPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+972500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('972500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ILPhone::make('0500000000')->toString())->toEqual('+972500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => ILPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => ILPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ILPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => ILPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => ILPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => ILPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => ILPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ILPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ILPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IL');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ILPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IL')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ILPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
