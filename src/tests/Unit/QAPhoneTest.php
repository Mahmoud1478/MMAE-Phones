<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\QAPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\QAPlaceholder;
use MMAE\Phones\Rules\QAPhoneRule;

test('can create a phone object', function () {
    expect(QAPhone::make('030000000'))->toBeInstanceOf(QAPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(QAPhone::make($number)->isValid())->toBeTrue();
})->with(['97430000000', '97450000000', '97460000000', '97470000000']);

test('is valid with the local key', function () {
    expect(QAPhone::make('030000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(QAPhone::make('97430000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(QAPhone::make('+97430000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(QAPhone::make('0097430000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(QAPhone::make('97430000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(QAPhone::make('97470000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = QAPhone::make('974 3-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('97430000000');
});

test('is not valid when too short', function () {
    expect(QAPhone::make('3000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(QAPhone::make('700000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(QAPhone::make('99930000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(QAPhone::make('000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(QAPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(QAPhone::make('030000000')->all())->toEqual(['+97430000000', '0097430000000', '97430000000', '030000000']);
});

test('toArray mirrors all', function () {
    $phone = QAPhone::make('030000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = QAPhone::make('97430000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('30000000');
});

test('config exposes the country schema', function () {
    $phone = QAPhone::make('030000000');
    expect($phone->config('key'))->toEqual('974')
        ->and($phone->config('code'))->toEqual('QA')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(QAPhone::make('974 3-0000000')->number())->toEqual('974 3-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = QAPhone::make('030000000');
    expect($phone->withPlus()->toString())->toEqual('+97430000000')
        ->and($phone->withoutPlus()->toString())->toEqual('97430000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(QAPhone::make('030000000')->toString())->toEqual('+97430000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '030000000'], ['phone' => QAPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => QAPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(QAPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '3000000'], ['phone' => QAPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '3000000'], ['phone' => QAPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '030000000'], ['phone' => QAPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '3000000'], ['phone' => QAPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = QAPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(QAPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('QA');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(QAPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('QA')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(QAPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
