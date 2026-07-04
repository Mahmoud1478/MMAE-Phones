<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\HKPhone;
use MMAE\Phones\Placeholders\HKPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\HKPhoneRule;

test('can create a phone object', function () {
    expect(HKPhone::make('50000000'))->toBeInstanceOf(HKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(HKPhone::make($number)->isValid())->toBeTrue();
})->with(['85250000000', '85260000000', '85270000000', '85280000000', '85290000000']);

test('is valid with the local key', function () {
    expect(HKPhone::make('50000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(HKPhone::make('85250000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(HKPhone::make('+85250000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(HKPhone::make('0085250000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(HKPhone::make('85250000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(HKPhone::make('85290000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = HKPhone::make('852 5-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('85250000000');
});

test('is not valid when too short', function () {
    expect(HKPhone::make('5000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(HKPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(HKPhone::make('99950000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(HKPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(HKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(HKPhone::make('50000000')->all())->toEqual(['+85250000000', '0085250000000', '85250000000']);
});

test('toArray mirrors all', function () {
    $phone = HKPhone::make('50000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = HKPhone::make('85250000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('50000000');
});

test('config exposes the country schema', function () {
    $phone = HKPhone::make('50000000');
    expect($phone->config('key'))->toEqual('852')
        ->and($phone->config('code'))->toEqual('HK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(HKPhone::make('852 5-0000000')->number())->toEqual('852 5-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = HKPhone::make('50000000');
    expect($phone->withPlus()->toString())->toEqual('+85250000000')
        ->and($phone->withoutPlus()->toString())->toEqual('85250000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(HKPhone::make('50000000')->toString())->toEqual('+85250000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '50000000'], ['phone' => HKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => HKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(HKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '5000000'], ['phone' => HKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '5000000'], ['phone' => HKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '50000000'], ['phone' => HKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '5000000'], ['phone' => HKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = HKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(HKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('HK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(HKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('HK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(HKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
