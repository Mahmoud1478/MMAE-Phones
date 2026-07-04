<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\NEPhone;
use MMAE\Phones\Placeholders\NEPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\NEPhoneRule;

test('can create a phone object', function () {
    expect(NEPhone::make('80000000'))->toBeInstanceOf(NEPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(NEPhone::make($number)->isValid())->toBeTrue();
})->with(['22780000000', '22790000000']);

test('is valid with the local key', function () {
    expect(NEPhone::make('80000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(NEPhone::make('22780000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(NEPhone::make('+22780000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(NEPhone::make('0022780000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(NEPhone::make('22780000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(NEPhone::make('22790000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = NEPhone::make('227 8-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('22780000000');
});

test('is not valid when too short', function () {
    expect(NEPhone::make('8000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(NEPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(NEPhone::make('99980000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(NEPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(NEPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(NEPhone::make('80000000')->all())->toEqual(['+22780000000', '0022780000000', '22780000000']);
});

test('toArray mirrors all', function () {
    $phone = NEPhone::make('80000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = NEPhone::make('22780000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('80000000');
});

test('config exposes the country schema', function () {
    $phone = NEPhone::make('80000000');
    expect($phone->config('key'))->toEqual('227')
        ->and($phone->config('code'))->toEqual('NE')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(NEPhone::make('227 8-0000000')->number())->toEqual('227 8-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = NEPhone::make('80000000');
    expect($phone->withPlus()->toString())->toEqual('+22780000000')
        ->and($phone->withoutPlus()->toString())->toEqual('22780000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(NEPhone::make('80000000')->toString())->toEqual('+22780000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '80000000'], ['phone' => NEPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => NEPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(NEPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '8000000'], ['phone' => NEPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '8000000'], ['phone' => NEPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '80000000'], ['phone' => NEPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '8000000'], ['phone' => NEPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = NEPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(NEPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('NE');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(NEPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('NE')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(NEPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
