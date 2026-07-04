<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\DZPhone;
use MMAE\Phones\Placeholders\DZPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\DZPhoneRule;

test('can create a phone object', function () {
    expect(DZPhone::make('0500000000'))->toBeInstanceOf(DZPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(DZPhone::make($number)->isValid())->toBeTrue();
})->with(['213500000000', '213510000000', '213520000000', '213530000000', '213540000000', '213550000000', '213560000000', '213570000000', '213580000000', '213590000000', '213600000000', '213610000000', '213620000000', '213630000000', '213640000000', '213650000000', '213660000000', '213670000000', '213680000000', '213690000000', '213700000000', '213710000000', '213720000000', '213730000000', '213740000000', '213750000000', '213760000000', '213770000000', '213780000000', '213790000000']);

test('is valid with the local key', function () {
    expect(DZPhone::make('0500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(DZPhone::make('213500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(DZPhone::make('+213500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(DZPhone::make('00213500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(DZPhone::make('213500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(DZPhone::make('213790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = DZPhone::make('213 5-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('213500000000');
});

test('is not valid when too short', function () {
    expect(DZPhone::make('50000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(DZPhone::make('7900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(DZPhone::make('999500000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(DZPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(DZPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(DZPhone::make('0500000000')->all())->toEqual(['+213500000000', '00213500000000', '213500000000', '0500000000']);
});

test('toArray mirrors all', function () {
    $phone = DZPhone::make('0500000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = DZPhone::make('213500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('500000000');
});

test('config exposes the country schema', function () {
    $phone = DZPhone::make('0500000000');
    expect($phone->config('key'))->toEqual('213')
        ->and($phone->config('code'))->toEqual('DZ')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(DZPhone::make('213 5-00000000')->number())->toEqual('213 5-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = DZPhone::make('0500000000');
    expect($phone->withPlus()->toString())->toEqual('+213500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('213500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(DZPhone::make('0500000000')->toString())->toEqual('+213500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '0500000000'], ['phone' => DZPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => DZPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(DZPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '50000000'], ['phone' => DZPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '50000000'], ['phone' => DZPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '0500000000'], ['phone' => DZPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => DZPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = DZPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(DZPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('DZ');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(DZPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('DZ')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(DZPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
