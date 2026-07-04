<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SZPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SZPlaceholder;
use MMAE\Phones\Rules\SZPhoneRule;

test('can create a phone object', function () {
    expect(SZPhone::make('76000000'))->toBeInstanceOf(SZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SZPhone::make($number)->isValid())->toBeTrue();
})->with(['26876000000', '26877000000', '26878000000']);

test('is valid with the local key', function () {
    expect(SZPhone::make('76000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SZPhone::make('26876000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SZPhone::make('+26876000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SZPhone::make('0026876000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SZPhone::make('26876000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SZPhone::make('26878000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SZPhone::make('268 7-6000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('26876000000');
});

test('is not valid when too short', function () {
    expect(SZPhone::make('7600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SZPhone::make('780000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SZPhone::make('99976000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SZPhone::make('06000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SZPhone::make('76000000')->all())->toEqual(['+26876000000', '0026876000000', '26876000000']);
});

test('toArray mirrors all', function () {
    $phone = SZPhone::make('76000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SZPhone::make('26876000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('76000000');
});

test('config exposes the country schema', function () {
    $phone = SZPhone::make('76000000');
    expect($phone->config('key'))->toEqual('268')
        ->and($phone->config('code'))->toEqual('SZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SZPhone::make('268 7-6000000')->number())->toEqual('268 7-6000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SZPhone::make('76000000');
    expect($phone->withPlus()->toString())->toEqual('+26876000000')
        ->and($phone->withoutPlus()->toString())->toEqual('26876000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SZPhone::make('76000000')->toString())->toEqual('+26876000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '76000000'], ['phone' => SZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '7600000'], ['phone' => SZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '7600000'], ['phone' => SZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '7600000'], ['phone' => SZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '76000000'], ['phone' => SZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '7600000'], ['phone' => SZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
