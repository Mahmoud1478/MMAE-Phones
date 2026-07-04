<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BZPhone;
use MMAE\Phones\Placeholders\BZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BZPhoneRule;

test('can create a phone object', function () {
    expect(BZPhone::make('6000000'))->toBeInstanceOf(BZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BZPhone::make($number)->isValid())->toBeTrue();
})->with(['5016000000']);

test('is valid with the local key', function () {
    expect(BZPhone::make('6000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BZPhone::make('5016000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BZPhone::make('+5016000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BZPhone::make('005016000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BZPhone::make('5016000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BZPhone::make('5016000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BZPhone::make('501 6-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('5016000000');
});

test('is not valid when too short', function () {
    expect(BZPhone::make('600000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BZPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BZPhone::make('9996000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BZPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BZPhone::make('6000000')->all())->toEqual(['+5016000000', '005016000000', '5016000000']);
});

test('toArray mirrors all', function () {
    $phone = BZPhone::make('6000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BZPhone::make('5016000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('6000000');
});

test('config exposes the country schema', function () {
    $phone = BZPhone::make('6000000');
    expect($phone->config('key'))->toEqual('501')
        ->and($phone->config('code'))->toEqual('BZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BZPhone::make('501 6-000000')->number())->toEqual('501 6-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BZPhone::make('6000000');
    expect($phone->withPlus()->toString())->toEqual('+5016000000')
        ->and($phone->withoutPlus()->toString())->toEqual('5016000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BZPhone::make('6000000')->toString())->toEqual('+5016000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '6000000'], ['phone' => BZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => BZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '600000'], ['phone' => BZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '600000'], ['phone' => BZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '6000000'], ['phone' => BZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '600000'], ['phone' => BZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
