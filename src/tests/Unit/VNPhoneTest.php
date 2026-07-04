<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VNPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VNPlaceholder;
use MMAE\Phones\Rules\VNPhoneRule;

test('can create a phone object', function () {
    expect(VNPhone::make('0320000000'))->toBeInstanceOf(VNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VNPhone::make($number)->isValid())->toBeTrue();
})->with(['84320000000', '84330000000', '84340000000', '84350000000', '84360000000', '84370000000', '84380000000', '84390000000', '84520000000', '84560000000', '84580000000', '84590000000', '84700000000', '84760000000', '84770000000', '84780000000', '84790000000', '84810000000', '84820000000', '84830000000', '84840000000', '84850000000', '84860000000', '84870000000', '84880000000', '84890000000', '84900000000', '84910000000', '84920000000', '84930000000', '84940000000', '84950000000']);

test('is valid with the local key', function () {
    expect(VNPhone::make('0320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VNPhone::make('84320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VNPhone::make('+84320000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VNPhone::make('0084320000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VNPhone::make('84320000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VNPhone::make('84950000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VNPhone::make('84 3-20000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('84320000000');
});

test('is not valid when too short', function () {
    expect(VNPhone::make('32000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VNPhone::make('9500000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VNPhone::make('999320000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VNPhone::make('0020000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VNPhone::make('0320000000')->all())->toEqual(['+84320000000', '0084320000000', '84320000000', '0320000000']);
});

test('toArray mirrors all', function () {
    $phone = VNPhone::make('0320000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VNPhone::make('84320000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('320000000');
});

test('config exposes the country schema', function () {
    $phone = VNPhone::make('0320000000');
    expect($phone->config('key'))->toEqual('84')
        ->and($phone->config('code'))->toEqual('VN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VNPhone::make('84 3-20000000')->number())->toEqual('84 3-20000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VNPhone::make('0320000000');
    expect($phone->withPlus()->toString())->toEqual('+84320000000')
        ->and($phone->withoutPlus()->toString())->toEqual('84320000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VNPhone::make('0320000000')->toString())->toEqual('+84320000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0320000000'], ['phone' => VNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '32000000'], ['phone' => VNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '32000000'], ['phone' => VNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '32000000'], ['phone' => VNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0320000000'], ['phone' => VNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '32000000'], ['phone' => VNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
