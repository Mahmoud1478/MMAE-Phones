<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ZAPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\ZAPlaceholder;
use MMAE\Phones\Rules\ZAPhoneRule;

test('can create a phone object', function () {
    expect(ZAPhone::make('0600000000'))->toBeInstanceOf(ZAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ZAPhone::make($number)->isValid())->toBeTrue();
})->with(['27600000000', '27610000000', '27620000000', '27630000000', '27640000000', '27650000000', '27660000000', '27670000000', '27680000000', '27690000000', '27700000000', '27710000000', '27720000000', '27730000000', '27740000000', '27750000000', '27760000000', '27770000000', '27780000000', '27790000000', '27800000000', '27810000000', '27820000000', '27830000000', '27840000000', '27850000000', '27860000000', '27870000000', '27880000000', '27890000000']);

test('is valid with the local key', function () {
    expect(ZAPhone::make('0600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ZAPhone::make('27600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ZAPhone::make('+27600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ZAPhone::make('0027600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ZAPhone::make('27600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ZAPhone::make('27890000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ZAPhone::make('27 6-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('27600000000');
});

test('is not valid when too short', function () {
    expect(ZAPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ZAPhone::make('8900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ZAPhone::make('999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ZAPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ZAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ZAPhone::make('0600000000')->all())->toEqual(['+27600000000', '0027600000000', '27600000000', '0600000000']);
});

test('toArray mirrors all', function () {
    $phone = ZAPhone::make('0600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ZAPhone::make('27600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('600000000');
});

test('config exposes the country schema', function () {
    $phone = ZAPhone::make('0600000000');
    expect($phone->config('key'))->toEqual('27')
        ->and($phone->config('code'))->toEqual('ZA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ZAPhone::make('27 6-00000000')->number())->toEqual('27 6-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ZAPhone::make('0600000000');
    expect($phone->withPlus()->toString())->toEqual('+27600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('27600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ZAPhone::make('0600000000')->toString())->toEqual('+27600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0600000000'], ['phone' => ZAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ZAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ZAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ZAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '60000000'], ['phone' => ZAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0600000000'], ['phone' => ZAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => ZAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ZAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ZAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('ZA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ZAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('ZA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ZAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
