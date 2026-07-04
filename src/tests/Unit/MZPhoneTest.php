<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\MZPhone;
use MMAE\Phones\Placeholders\MZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\MZPhoneRule;

test('can create a phone object', function () {
    expect(MZPhone::make('820000000'))->toBeInstanceOf(MZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(MZPhone::make($number)->isValid())->toBeTrue();
})->with(['258820000000', '258830000000', '258840000000', '258850000000', '258860000000', '258870000000']);

test('is valid with the local key', function () {
    expect(MZPhone::make('820000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(MZPhone::make('258820000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(MZPhone::make('+258820000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(MZPhone::make('00258820000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(MZPhone::make('258820000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(MZPhone::make('258870000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = MZPhone::make('258 8-20000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('258820000000');
});

test('is not valid when too short', function () {
    expect(MZPhone::make('82000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(MZPhone::make('8700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(MZPhone::make('999820000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(MZPhone::make('020000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(MZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(MZPhone::make('820000000')->all())->toEqual(['+258820000000', '00258820000000', '258820000000']);
});

test('toArray mirrors all', function () {
    $phone = MZPhone::make('820000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = MZPhone::make('258820000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('820000000');
});

test('config exposes the country schema', function () {
    $phone = MZPhone::make('820000000');
    expect($phone->config('key'))->toEqual('258')
        ->and($phone->config('code'))->toEqual('MZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(MZPhone::make('258 8-20000000')->number())->toEqual('258 8-20000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = MZPhone::make('820000000');
    expect($phone->withPlus()->toString())->toEqual('+258820000000')
        ->and($phone->withoutPlus()->toString())->toEqual('258820000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(MZPhone::make('820000000')->toString())->toEqual('+258820000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '820000000'], ['phone' => MZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '82000000'], ['phone' => MZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(MZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '82000000'], ['phone' => MZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '82000000'], ['phone' => MZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '820000000'], ['phone' => MZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '82000000'], ['phone' => MZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = MZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(MZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('MZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(MZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('MZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(MZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
