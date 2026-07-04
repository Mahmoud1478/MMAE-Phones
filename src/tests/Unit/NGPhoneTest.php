<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NGPhone;
use MMAE\Phones\Placeholders\NGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NGPhoneRule;

test('can create a phone object', function () {
    expect(NGPhone::make('07000000000'))->toBeInstanceOf(NGPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NGPhone::make($number)->isValid())->toBeTrue();
})->with(['2347000000000', '2347100000000', '2347200000000', '2347300000000', '2347400000000', '2347500000000', '2347600000000', '2347700000000', '2347800000000', '2347900000000', '2348000000000', '2348100000000', '2348200000000', '2348300000000', '2348400000000', '2348500000000', '2348600000000', '2348700000000', '2348800000000', '2348900000000', '2349000000000', '2349100000000', '2349200000000', '2349300000000', '2349400000000', '2349500000000', '2349600000000', '2349700000000', '2349800000000', '2349900000000']);

test('is valid with the local key', function () {
    expect(NGPhone::make('07000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NGPhone::make('2347000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NGPhone::make('+2347000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NGPhone::make('002347000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NGPhone::make('2347000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NGPhone::make('2349900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NGPhone::make('234 7-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('2347000000000');
});

test('is not valid when too short', function () {
    expect(NGPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NGPhone::make('99000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NGPhone::make('9997000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NGPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NGPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NGPhone::make('07000000000')->all())->toEqual(['+2347000000000', '002347000000000', '2347000000000', '07000000000']);
});

test('toArray mirrors all', function () {
    $phone = NGPhone::make('07000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NGPhone::make('2347000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('7000000000');
});

test('config exposes the country schema', function () {
    $phone = NGPhone::make('07000000000');
    expect($phone->config('key'))->toEqual('234')
        ->and($phone->config('code'))->toEqual('NG')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NGPhone::make('234 7-000000000')->number())->toEqual('234 7-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NGPhone::make('07000000000');
    expect($phone->withPlus()->toString())->toEqual('+2347000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('2347000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NGPhone::make('07000000000')->toString())->toEqual('+2347000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '07000000000'], ['phone' => NGPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => NGPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NGPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '700000000'], ['phone' => NGPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '700000000'], ['phone' => NGPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '07000000000'], ['phone' => NGPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '700000000'], ['phone' => NGPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NGPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NGPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NG');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NGPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NG')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NGPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
