<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\GNPhone;
use MMAE\Phones\Placeholders\GNPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\GNPhoneRule;

test('can create a phone object', function () {
    expect(GNPhone::make('600000000'))->toBeInstanceOf(GNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(GNPhone::make($number)->isValid())->toBeTrue();
})->with(['224600000000', '224610000000', '224620000000', '224630000000', '224640000000', '224650000000', '224660000000', '224670000000', '224680000000', '224690000000']);

test('is valid with the local key', function () {
    expect(GNPhone::make('600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(GNPhone::make('224600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(GNPhone::make('+224600000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(GNPhone::make('00224600000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(GNPhone::make('224600000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(GNPhone::make('224690000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = GNPhone::make('224 6-00000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('224600000000');
});

test('is not valid when too short', function () {
    expect(GNPhone::make('60000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(GNPhone::make('6900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(GNPhone::make('999600000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(GNPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(GNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(GNPhone::make('600000000')->all())->toEqual(['+224600000000', '00224600000000', '224600000000']);
});

test('toArray mirrors all', function () {
    $phone = GNPhone::make('600000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = GNPhone::make('224600000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('600000000');
});

test('config exposes the country schema', function () {
    $phone = GNPhone::make('600000000');
    expect($phone->config('key'))->toEqual('224')
        ->and($phone->config('code'))->toEqual('GN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(GNPhone::make('224 6-00000000')->number())->toEqual('224 6-00000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = GNPhone::make('600000000');
    expect($phone->withPlus()->toString())->toEqual('+224600000000')
        ->and($phone->withoutPlus()->toString())->toEqual('224600000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(GNPhone::make('600000000')->toString())->toEqual('+224600000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '600000000'], ['phone' => GNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => GNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(GNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '60000000'], ['phone' => GNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '60000000'], ['phone' => GNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '600000000'], ['phone' => GNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '60000000'], ['phone' => GNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = GNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(GNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('GN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(GNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('GN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(GNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
