<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LBPhone;
use MMAE\Phones\Placeholders\LBPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LBPhoneRule;

test('can create a phone object', function () {
    expect(LBPhone::make('03000000'))->toBeInstanceOf(LBPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LBPhone::make($number)->isValid())->toBeTrue();
})->with(['9613000000', '96130000000', '96170000000', '96171000000', '96176000000', '96178000000', '96179000000', '961700000000', '961710000000', '961760000000', '961780000000', '961790000000']);

test('is valid with the local key', function () {
    expect(LBPhone::make('03000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LBPhone::make('9613000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LBPhone::make('+9613000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LBPhone::make('009613000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LBPhone::make('9613000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LBPhone::make('961790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LBPhone::make('961 3-000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('9613000000');
});

test('is not valid when too short', function () {
    expect(LBPhone::make('300000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LBPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LBPhone::make('9993000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LBPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LBPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LBPhone::make('03000000')->all())->toEqual(['+9613000000', '009613000000', '9613000000', '03000000']);
});

test('toArray mirrors all', function () {
    $phone = LBPhone::make('03000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LBPhone::make('9613000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3000000');
});

test('config exposes the country schema', function () {
    $phone = LBPhone::make('03000000');
    expect($phone->config('key'))->toEqual('961')
        ->and($phone->config('code'))->toEqual('LB')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LBPhone::make('961 3-000000')->number())->toEqual('961 3-000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LBPhone::make('03000000');
    expect($phone->withPlus()->toString())->toEqual('+9613000000')
        ->and($phone->withoutPlus()->toString())->toEqual('9613000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LBPhone::make('03000000')->toString())->toEqual('+9613000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '03000000'], ['phone' => LBPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '300000'], ['phone' => LBPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LBPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '300000'], ['phone' => LBPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '300000'], ['phone' => LBPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '03000000'], ['phone' => LBPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '300000'], ['phone' => LBPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LBPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LBPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LB');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LBPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LB')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LBPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
