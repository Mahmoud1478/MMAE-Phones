<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\XKPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\XKPlaceholder;
use MMAE\Phones\Rules\XKPhoneRule;

test('can create a phone object', function () {
    expect(XKPhone::make('043000000'))->toBeInstanceOf(XKPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(XKPhone::make($number)->isValid())->toBeTrue();
})->with(['38343000000', '38344000000', '38345000000', '38346000000', '38347000000', '38348000000', '38349000000']);

test('is valid with the local key', function () {
    expect(XKPhone::make('043000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(XKPhone::make('38343000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(XKPhone::make('+38343000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(XKPhone::make('0038343000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(XKPhone::make('38343000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(XKPhone::make('38349000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = XKPhone::make('383 4-3000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('38343000000');
});

test('is not valid when too short', function () {
    expect(XKPhone::make('4300000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(XKPhone::make('490000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(XKPhone::make('99943000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(XKPhone::make('003000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(XKPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(XKPhone::make('043000000')->all())->toEqual(['+38343000000', '0038343000000', '38343000000', '043000000']);
});

test('toArray mirrors all', function () {
    $phone = XKPhone::make('043000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = XKPhone::make('38343000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('43000000');
});

test('config exposes the country schema', function () {
    $phone = XKPhone::make('043000000');
    expect($phone->config('key'))->toEqual('383')
        ->and($phone->config('code'))->toEqual('XK')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(XKPhone::make('383 4-3000000')->number())->toEqual('383 4-3000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = XKPhone::make('043000000');
    expect($phone->withPlus()->toString())->toEqual('+38343000000')
        ->and($phone->withoutPlus()->toString())->toEqual('38343000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(XKPhone::make('043000000')->toString())->toEqual('+38343000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '043000000'], ['phone' => XKPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '4300000'], ['phone' => XKPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(XKPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '4300000'], ['phone' => XKPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '4300000'], ['phone' => XKPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '043000000'], ['phone' => XKPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '4300000'], ['phone' => XKPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = XKPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(XKPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('XK');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(XKPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('XK')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(XKPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
