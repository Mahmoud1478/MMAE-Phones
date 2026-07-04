<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\LRPhone;
use MMAE\Phones\Placeholders\LRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\LRPhoneRule;

test('can create a phone object', function () {
    expect(LRPhone::make('040000000'))->toBeInstanceOf(LRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(LRPhone::make($number)->isValid())->toBeTrue();
})->with(['23140000000', '23150000000', '23160000000', '23170000000', '23180000000', '231400000000', '231500000000', '231600000000', '231700000000', '231800000000']);

test('is valid with the local key', function () {
    expect(LRPhone::make('040000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(LRPhone::make('23140000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(LRPhone::make('+23140000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(LRPhone::make('0023140000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(LRPhone::make('23140000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(LRPhone::make('231800000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = LRPhone::make('231 4-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('23140000000');
});

test('is not valid when too short', function () {
    expect(LRPhone::make('4000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(LRPhone::make('8000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(LRPhone::make('99940000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(LRPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(LRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(LRPhone::make('040000000')->all())->toEqual(['+23140000000', '0023140000000', '23140000000', '040000000']);
});

test('toArray mirrors all', function () {
    $phone = LRPhone::make('040000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = LRPhone::make('23140000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('40000000');
});

test('config exposes the country schema', function () {
    $phone = LRPhone::make('040000000');
    expect($phone->config('key'))->toEqual('231')
        ->and($phone->config('code'))->toEqual('LR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(LRPhone::make('231 4-0000000')->number())->toEqual('231 4-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = LRPhone::make('040000000');
    expect($phone->withPlus()->toString())->toEqual('+23140000000')
        ->and($phone->withoutPlus()->toString())->toEqual('23140000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(LRPhone::make('040000000')->toString())->toEqual('+23140000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '040000000'], ['phone' => LRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '4000000'], ['phone' => LRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(LRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '4000000'], ['phone' => LRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '4000000'], ['phone' => LRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '040000000'], ['phone' => LRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '4000000'], ['phone' => LRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = LRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(LRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('LR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(LRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('LR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(LRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
