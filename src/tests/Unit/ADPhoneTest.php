<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\ADPhone;
use MMAE\Phones\Placeholders\ADPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\ADPhoneRule;

test('can create a phone object', function () {
    expect(ADPhone::make('300000'))->toBeInstanceOf(ADPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(ADPhone::make($number)->isValid())->toBeTrue();
})->with(['376300000', '376400000', '376500000', '376600000']);

test('is valid with the local key', function () {
    expect(ADPhone::make('300000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(ADPhone::make('376300000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(ADPhone::make('+376300000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(ADPhone::make('00376300000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(ADPhone::make('376300000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(ADPhone::make('376600000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = ADPhone::make('376 3-00000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('376300000');
});

test('is not valid when too short', function () {
    expect(ADPhone::make('30000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(ADPhone::make('6000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(ADPhone::make('999300000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(ADPhone::make('000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(ADPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(ADPhone::make('300000')->all())->toEqual(['+376300000', '00376300000', '376300000']);
});

test('toArray mirrors all', function () {
    $phone = ADPhone::make('300000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = ADPhone::make('376300000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('300000');
});

test('config exposes the country schema', function () {
    $phone = ADPhone::make('300000');
    expect($phone->config('key'))->toEqual('376')
        ->and($phone->config('code'))->toEqual('AD')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(ADPhone::make('376 3-00000')->number())->toEqual('376 3-00000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = ADPhone::make('300000');
    expect($phone->withPlus()->toString())->toEqual('+376300000')
        ->and($phone->withoutPlus()->toString())->toEqual('376300000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(ADPhone::make('300000')->toString())->toEqual('+376300000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '300000'], ['phone' => ADPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '30000'], ['phone' => ADPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(ADPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '30000'], ['phone' => ADPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '30000'], ['phone' => ADPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '300000'], ['phone' => ADPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '30000'], ['phone' => ADPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = ADPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(ADPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AD');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(ADPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AD')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(ADPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
