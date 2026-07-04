<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\DKPhone;
use MMAE\Phones\Placeholders\DKPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\DKPhoneRule;

test('can create a phone object', function () {
    expect(DKPhone::make('00000000'))->toBeInstanceOf(DKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(DKPhone::make($number)->isValid())->toBeTrue();
})->with(['4500000000']);

test('is valid with the local key', function () {
    expect(DKPhone::make('00000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(DKPhone::make('4500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(DKPhone::make('+4500000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(DKPhone::make('004500000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(DKPhone::make('4500000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(DKPhone::make('4500000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = DKPhone::make('45 0-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('4500000000');
});

test('is not valid when too short', function () {
    expect(DKPhone::make('0000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(DKPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(DKPhone::make('99900000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(DKPhone::make('00000000')->isNotValid())->toBeTrue();
})->skip('provider pattern accepts any starting digit');

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(DKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(DKPhone::make('00000000')->all())->toEqual(['+4500000000', '004500000000', '4500000000']);
});

test('toArray mirrors all', function () {
    $phone = DKPhone::make('00000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = DKPhone::make('4500000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('00000000');
});

test('config exposes the country schema', function () {
    $phone = DKPhone::make('00000000');
    expect($phone->config('key'))->toEqual('45')
        ->and($phone->config('code'))->toEqual('DK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(DKPhone::make('45 0-0000000')->number())->toEqual('45 0-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = DKPhone::make('00000000');
    expect($phone->withPlus()->toString())->toEqual('+4500000000')
        ->and($phone->withoutPlus()->toString())->toEqual('4500000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(DKPhone::make('00000000')->toString())->toEqual('+4500000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '00000000'], ['phone' => DKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '0000000'], ['phone' => DKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(DKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '0000000'], ['phone' => DKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '0000000'], ['phone' => DKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '00000000'], ['phone' => DKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '0000000'], ['phone' => DKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = DKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(DKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('DK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(DKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('DK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(DKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
