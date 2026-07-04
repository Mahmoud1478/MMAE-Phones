<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\AMPhone;
use MMAE\Phones\Placeholders\AMPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\AMPhoneRule;

test('can create a phone object', function () {
    expect(AMPhone::make('040000000'))->toBeInstanceOf(AMPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(AMPhone::make($number)->isValid())->toBeTrue();
})->with(['37440000000', '37441000000', '37442000000', '37443000000', '37444000000', '37445000000', '37446000000', '37447000000', '37448000000', '37449000000', '37450000000', '37451000000', '37452000000', '37453000000', '37454000000', '37455000000', '37456000000', '37457000000', '37458000000', '37459000000', '37460000000', '37461000000', '37462000000', '37463000000', '37464000000', '37465000000', '37466000000', '37467000000', '37468000000', '37469000000', '37470000000', '37471000000']);

test('is valid with the local key', function () {
    expect(AMPhone::make('040000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(AMPhone::make('37440000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(AMPhone::make('+37440000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(AMPhone::make('0037440000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(AMPhone::make('37440000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(AMPhone::make('37471000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = AMPhone::make('374 4-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('37440000000');
});

test('is not valid when too short', function () {
    expect(AMPhone::make('4000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(AMPhone::make('710000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(AMPhone::make('99940000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(AMPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(AMPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(AMPhone::make('040000000')->all())->toEqual(['+37440000000', '0037440000000', '37440000000', '040000000']);
});

test('toArray mirrors all', function () {
    $phone = AMPhone::make('040000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = AMPhone::make('37440000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('40000000');
});

test('config exposes the country schema', function () {
    $phone = AMPhone::make('040000000');
    expect($phone->config('key'))->toEqual('374')
        ->and($phone->config('code'))->toEqual('AM')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(AMPhone::make('374 4-0000000')->number())->toEqual('374 4-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = AMPhone::make('040000000');
    expect($phone->withPlus()->toString())->toEqual('+37440000000')
        ->and($phone->withoutPlus()->toString())->toEqual('37440000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(AMPhone::make('040000000')->toString())->toEqual('+37440000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '040000000'], ['phone' => AMPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '4000000'], ['phone' => AMPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(AMPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '4000000'], ['phone' => AMPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '4000000'], ['phone' => AMPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '040000000'], ['phone' => AMPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '4000000'], ['phone' => AMPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = AMPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(AMPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('AM');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(AMPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('AM')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(AMPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
