<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\BRPhone;
use MMAE\Phones\Placeholders\BRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\BRPhoneRule;

test('can create a phone object', function () {
    expect(BRPhone::make('01100000000'))->toBeInstanceOf(BRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(BRPhone::make($number)->isValid())->toBeTrue();
})->with(['551100000000', '551200000000', '551300000000', '551400000000', '551500000000', '551600000000', '551700000000', '551800000000', '551900000000', '552100000000', '552200000000', '552300000000', '552400000000', '552500000000', '552600000000', '552700000000', '5511000000000', '5512000000000', '5513000000000', '5514000000000', '5515000000000', '5516000000000', '5517000000000', '5518000000000', '5519000000000', '5521000000000', '5522000000000', '5523000000000', '5524000000000', '5525000000000', '5526000000000', '5527000000000']);

test('is valid with the local key', function () {
    expect(BRPhone::make('01100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(BRPhone::make('551100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(BRPhone::make('+551100000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(BRPhone::make('00551100000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(BRPhone::make('551100000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(BRPhone::make('5527000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = BRPhone::make('55 1-100000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('551100000000');
});

test('is not valid when too short', function () {
    expect(BRPhone::make('110000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(BRPhone::make('270000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(BRPhone::make('9991100000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(BRPhone::make('00100000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(BRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(BRPhone::make('01100000000')->all())->toEqual(['+551100000000', '00551100000000', '551100000000', '01100000000']);
});

test('toArray mirrors all', function () {
    $phone = BRPhone::make('01100000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = BRPhone::make('551100000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('1100000000');
});

test('config exposes the country schema', function () {
    $phone = BRPhone::make('01100000000');
    expect($phone->config('key'))->toEqual('55')
        ->and($phone->config('code'))->toEqual('BR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(BRPhone::make('55 1-100000000')->number())->toEqual('55 1-100000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = BRPhone::make('01100000000');
    expect($phone->withPlus()->toString())->toEqual('+551100000000')
        ->and($phone->withoutPlus()->toString())->toEqual('551100000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(BRPhone::make('01100000000')->toString())->toEqual('+551100000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '01100000000'], ['phone' => BRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '110000000'], ['phone' => BRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(BRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '110000000'], ['phone' => BRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '110000000'], ['phone' => BRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '01100000000'], ['phone' => BRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '110000000'], ['phone' => BRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = BRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(BRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('BR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(BRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('BR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(BRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
