<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MRPhone;
use MMAE\Phones\Placeholders\MRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MRPhoneRule;

test('can create a phone object', function () {
    expect(MRPhone::make('20000000'))->toBeInstanceOf(MRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MRPhone::make($number)->isValid())->toBeTrue();
})->with(['22220000000', '22230000000', '22240000000']);

test('is valid with the local key', function () {
    expect(MRPhone::make('20000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MRPhone::make('22220000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MRPhone::make('+22220000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MRPhone::make('0022220000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MRPhone::make('22220000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MRPhone::make('22240000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MRPhone::make('222 2-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('22220000000');
});

test('is not valid when too short', function () {
    expect(MRPhone::make('2000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MRPhone::make('400000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MRPhone::make('99920000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MRPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MRPhone::make('20000000')->all())->toEqual(['+22220000000', '0022220000000', '22220000000']);
});

test('toArray mirrors all', function () {
    $phone = MRPhone::make('20000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MRPhone::make('22220000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('20000000');
});

test('config exposes the country schema', function () {
    $phone = MRPhone::make('20000000');
    expect($phone->config('key'))->toEqual('222')
        ->and($phone->config('code'))->toEqual('MR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MRPhone::make('222 2-0000000')->number())->toEqual('222 2-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MRPhone::make('20000000');
    expect($phone->withPlus()->toString())->toEqual('+22220000000')
        ->and($phone->withoutPlus()->toString())->toEqual('22220000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MRPhone::make('20000000')->toString())->toEqual('+22220000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => MRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => MRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '2000000'], ['phone' => MRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '2000000'], ['phone' => MRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '20000000'], ['phone' => MRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => MRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
