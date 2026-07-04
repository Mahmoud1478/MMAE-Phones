<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\KHPhone;
use MMAE\Phones\Placeholders\KHPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\KHPhoneRule;

test('can create a phone object', function () {
    expect(KHPhone::make('010000000'))->toBeInstanceOf(KHPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(KHPhone::make($number)->isValid())->toBeTrue();
})->with(['85510000000', '85520000000', '85530000000', '85540000000', '85550000000', '85560000000', '85570000000', '85580000000', '85590000000', '855100000000', '855200000000', '855300000000', '855400000000', '855500000000', '855600000000', '855700000000', '855800000000', '855900000000']);

test('is valid with the local key', function () {
    expect(KHPhone::make('010000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(KHPhone::make('85510000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(KHPhone::make('+85510000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(KHPhone::make('0085510000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(KHPhone::make('85510000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(KHPhone::make('855900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = KHPhone::make('855 1-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('85510000000');
});

test('is not valid when too short', function () {
    expect(KHPhone::make('1000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(KHPhone::make('9000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(KHPhone::make('99910000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(KHPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(KHPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(KHPhone::make('010000000')->all())->toEqual(['+85510000000', '0085510000000', '85510000000', '010000000']);
});

test('toArray mirrors all', function () {
    $phone = KHPhone::make('010000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = KHPhone::make('85510000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('10000000');
});

test('config exposes the country schema', function () {
    $phone = KHPhone::make('010000000');
    expect($phone->config('key'))->toEqual('855')
        ->and($phone->config('code'))->toEqual('KH')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(KHPhone::make('855 1-0000000')->number())->toEqual('855 1-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = KHPhone::make('010000000');
    expect($phone->withPlus()->toString())->toEqual('+85510000000')
        ->and($phone->withoutPlus()->toString())->toEqual('85510000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(KHPhone::make('010000000')->toString())->toEqual('+85510000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '010000000'], ['phone' => KHPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '1000000'], ['phone' => KHPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(KHPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '1000000'], ['phone' => KHPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '1000000'], ['phone' => KHPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '010000000'], ['phone' => KHPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '1000000'], ['phone' => KHPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = KHPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(KHPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('KH');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(KHPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('KH')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(KHPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
