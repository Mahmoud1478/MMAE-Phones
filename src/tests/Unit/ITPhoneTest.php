<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ITPhone;
use MMAE\Phones\Placeholders\ITPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ITPhoneRule;

test('can create a phone object', function () {
    expect(ITPhone::make('3000000000'))->toBeInstanceOf(ITPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ITPhone::make($number)->isValid())->toBeTrue();
})->with(['393000000000', '3930000000000']);

test('is valid with the local key', function () {
    expect(ITPhone::make('3000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ITPhone::make('393000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ITPhone::make('+393000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ITPhone::make('00393000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ITPhone::make('393000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ITPhone::make('3930000000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ITPhone::make('39 3-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('393000000000');
});

test('is not valid when too short', function () {
    expect(ITPhone::make('300000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ITPhone::make('300000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ITPhone::make('9993000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ITPhone::make('0000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ITPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ITPhone::make('3000000000')->all())->toEqual(['+393000000000', '00393000000000', '393000000000']);
});

test('toArray mirrors all', function () {
    $phone = ITPhone::make('3000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ITPhone::make('393000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('3000000000');
});

test('config exposes the country schema', function () {
    $phone = ITPhone::make('3000000000');
    expect($phone->config('key'))->toEqual('39')
        ->and($phone->config('code'))->toEqual('IT')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ITPhone::make('39 3-000000000')->number())->toEqual('39 3-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ITPhone::make('3000000000');
    expect($phone->withPlus()->toString())->toEqual('+393000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('393000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ITPhone::make('3000000000')->toString())->toEqual('+393000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '3000000000'], ['phone' => ITPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => ITPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ITPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '300000000'], ['phone' => ITPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '300000000'], ['phone' => ITPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '3000000000'], ['phone' => ITPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '300000000'], ['phone' => ITPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ITPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ITPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IT');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ITPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IT')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ITPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
