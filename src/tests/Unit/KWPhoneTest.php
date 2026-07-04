<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KWPhone;
use MMAE\Phones\Placeholders\KWPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KWPhoneRule;

test('can create a phone object', function () {
    expect(KWPhone::make('050000000'))->toBeInstanceOf(KWPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KWPhone::make($number)->isValid())->toBeTrue();
})->with(['96550000000', '96560000000', '96590000000']);

test('is valid with the local key', function () {
    expect(KWPhone::make('050000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KWPhone::make('96550000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KWPhone::make('+96550000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KWPhone::make('0096550000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KWPhone::make('96550000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KWPhone::make('96590000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KWPhone::make('965 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('96550000000');
});

test('is not valid when too short', function () {
    expect(KWPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KWPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KWPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KWPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KWPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KWPhone::make('050000000')->all())->toEqual(['+96550000000', '0096550000000', '96550000000', '050000000']);
});

test('toArray mirrors all', function () {
    $phone = KWPhone::make('050000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KWPhone::make('96550000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = KWPhone::make('050000000');
    expect($phone->config('key'))->toEqual('965')
        ->and($phone->config('code'))->toEqual('KW')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KWPhone::make('965 5-0000000')->number())->toEqual('965 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KWPhone::make('050000000');
    expect($phone->withPlus()->toString())->toEqual('+96550000000')
        ->and($phone->withoutPlus()->toString())->toEqual('96550000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KWPhone::make('050000000')->toString())->toEqual('+96550000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '050000000'], ['phone' => KWPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => KWPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KWPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => KWPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => KWPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '050000000'], ['phone' => KWPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => KWPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KWPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KWPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KW');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KWPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KW')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KWPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
