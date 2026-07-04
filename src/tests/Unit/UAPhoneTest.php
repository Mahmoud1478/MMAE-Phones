<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\UAPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\UAPlaceholder;
use MMAE\Phones\Rules\UAPhoneRule;

test('can create a phone object', function () {
    expect(UAPhone::make('0500000000'))->toBeInstanceOf(UAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(UAPhone::make($number)->isValid())->toBeTrue();
})->with(['380500000000', '380510000000', '380520000000', '380530000000', '380540000000', '380550000000', '380560000000', '380570000000', '380580000000', '380590000000', '380600000000', '380610000000', '380620000000', '380630000000', '380640000000', '380650000000', '380660000000', '380670000000', '380680000000', '380690000000', '380700000000', '380710000000', '380720000000', '380730000000', '380740000000', '380750000000', '380760000000', '380770000000', '380780000000', '380790000000', '380800000000', '380810000000']);

test('is valid with the local key', function () {
    expect(UAPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(UAPhone::make('380500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(UAPhone::make('+380500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(UAPhone::make('00380500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(UAPhone::make('380500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(UAPhone::make('380810000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = UAPhone::make('380 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('380500000000');
});

test('is not valid when too short', function () {
    expect(UAPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(UAPhone::make('8100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(UAPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(UAPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(UAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(UAPhone::make('0500000000')->all())->toEqual(['+380500000000', '00380500000000', '380500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = UAPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = UAPhone::make('380500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = UAPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('380')
        ->and($phone->config('code'))->toEqual('UA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(UAPhone::make('380 5-00000000')->number())->toEqual('380 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = UAPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+380500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('380500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(UAPhone::make('0500000000')->toString())->toEqual('+380500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => UAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => UAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(UAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => UAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => UAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => UAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => UAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = UAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(UAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('UA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(UAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('UA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(UAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
