<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GHPhone;
use MMAE\Phones\Placeholders\GHPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GHPhoneRule;

test('can create a phone object', function () {
    expect(GHPhone::make('0200000000'))->toBeInstanceOf(GHPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GHPhone::make($number)->isValid())->toBeTrue();
})->with(['233200000000', '233210000000', '233220000000', '233230000000', '233240000000', '233250000000', '233260000000', '233270000000', '233280000000', '233290000000', '233300000000', '233310000000', '233320000000', '233330000000', '233340000000', '233350000000', '233360000000', '233370000000', '233380000000', '233390000000', '233400000000', '233410000000', '233420000000', '233430000000', '233440000000', '233450000000', '233460000000', '233470000000', '233480000000', '233490000000', '233500000000', '233510000000']);

test('is valid with the local key', function () {
    expect(GHPhone::make('0200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GHPhone::make('233200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GHPhone::make('+233200000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GHPhone::make('00233200000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GHPhone::make('233200000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GHPhone::make('233510000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GHPhone::make('233 2-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('233200000000');
});

test('is not valid when too short', function () {
    expect(GHPhone::make('20000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GHPhone::make('5100000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GHPhone::make('999200000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GHPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GHPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GHPhone::make('0200000000')->all())->toEqual(['+233200000000', '00233200000000', '233200000000', '0200000000']);
});

test('toArray mirrors all', function () {
    $phone = GHPhone::make('0200000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GHPhone::make('233200000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('200000000');
});

test('config exposes the country schema', function () {
    $phone = GHPhone::make('0200000000');
    expect($phone->config('key'))->toEqual('233')
        ->and($phone->config('code'))->toEqual('GH')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GHPhone::make('233 2-00000000')->number())->toEqual('233 2-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GHPhone::make('0200000000');
    expect($phone->withPlus()->toString())->toEqual('+233200000000')
        ->and($phone->withoutPlus()->toString())->toEqual('233200000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GHPhone::make('0200000000')->toString())->toEqual('+233200000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0200000000'], ['phone' => GHPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => GHPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GHPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '20000000'], ['phone' => GHPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '20000000'], ['phone' => GHPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0200000000'], ['phone' => GHPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => GHPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GHPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GHPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GH');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GHPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GH')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GHPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
