<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\SEPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\SEPlaceholder;
use MMAE\Phones\Rules\SEPhoneRule;

test('can create a phone object', function () {
    expect(SEPhone::make('0700000000'))->toBeInstanceOf(SEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(SEPhone::make($number)->isValid())->toBeTrue();
})->with(['46700000000', '46720000000', '46730000000', '46760000000', '46770000000', '46780000000', '46790000000', '467000000000', '467200000000', '467300000000', '467600000000', '467700000000', '467800000000', '467900000000']);

test('is valid with the local key', function () {
    expect(SEPhone::make('0700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(SEPhone::make('46700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(SEPhone::make('+46700000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(SEPhone::make('0046700000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(SEPhone::make('46700000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(SEPhone::make('467900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = SEPhone::make('46 7-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('46700000000');
});

test('is not valid when too short', function () {
    expect(SEPhone::make('70000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(SEPhone::make('79000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(SEPhone::make('999700000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(SEPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(SEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(SEPhone::make('0700000000')->all())->toEqual(['+46700000000', '0046700000000', '46700000000', '0700000000']);
});

test('toArray mirrors all', function () {
    $phone = SEPhone::make('0700000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = SEPhone::make('46700000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('700000000');
});

test('config exposes the country schema', function () {
    $phone = SEPhone::make('0700000000');
    expect($phone->config('key'))->toEqual('46')
        ->and($phone->config('code'))->toEqual('SE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(SEPhone::make('46 7-00000000')->number())->toEqual('46 7-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = SEPhone::make('0700000000');
    expect($phone->withPlus()->toString())->toEqual('+46700000000')
        ->and($phone->withoutPlus()->toString())->toEqual('46700000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(SEPhone::make('0700000000')->toString())->toEqual('+46700000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0700000000'], ['phone' => SEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => SEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(SEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '70000000'], ['phone' => SEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '70000000'], ['phone' => SEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0700000000'], ['phone' => SEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '70000000'], ['phone' => SEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = SEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(SEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('SE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(SEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('SE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(SEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
