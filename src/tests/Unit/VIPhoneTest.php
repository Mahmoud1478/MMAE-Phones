<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\VIPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\VIPlaceholder;
use MMAE\Phones\Rules\VIPhoneRule;

test('can create a phone object', function () {
    expect(VIPhone::make('13400000000'))->toBeInstanceOf(VIPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(VIPhone::make($number)->isValid())->toBeTrue();
})->with(['13400000000']);

test('is valid with the local key', function () {
    expect(VIPhone::make('13400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(VIPhone::make('13400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(VIPhone::make('+13400000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(VIPhone::make('0013400000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(VIPhone::make('13400000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(VIPhone::make('13400000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = VIPhone::make('1 3-400000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('13400000000');
});

test('is not valid when too short', function () {
    expect(VIPhone::make('340000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(VIPhone::make('34000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(VIPhone::make('9993400000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(VIPhone::make('10400000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(VIPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(VIPhone::make('13400000000')->all())->toEqual(['+13400000000', '0013400000000', '13400000000']);
});

test('toArray mirrors all', function () {
    $phone = VIPhone::make('13400000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = VIPhone::make('13400000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3400000000');
});

test('config exposes the country schema', function () {
    $phone = VIPhone::make('13400000000');
    expect($phone->config('key'))->toEqual('1')
        ->and($phone->config('code'))->toEqual('VI')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(VIPhone::make('1 3-400000000')->number())->toEqual('1 3-400000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = VIPhone::make('13400000000');
    expect($phone->withPlus()->toString())->toEqual('+13400000000')
        ->and($phone->withoutPlus()->toString())->toEqual('13400000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(VIPhone::make('13400000000')->toString())->toEqual('+13400000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '13400000000'], ['phone' => VIPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '340000000'], ['phone' => VIPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(VIPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '340000000'], ['phone' => VIPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '340000000'], ['phone' => VIPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '13400000000'], ['phone' => VIPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '340000000'], ['phone' => VIPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = VIPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(VIPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('VI');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(VIPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('VI')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(VIPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
