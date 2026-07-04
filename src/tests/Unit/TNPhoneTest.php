<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\TNPhone;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Placeholders\TNPlaceholder;
use MMAE\Phones\Rules\TNPhoneRule;

test('can create a phone object', function () {
    expect(TNPhone::make('20000000'))->toBeInstanceOf(TNPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(TNPhone::make($number)->isValid())->toBeTrue();
})->with(['21620000000', '21621000000', '21622000000', '21623000000', '21624000000', '21625000000', '21626000000', '21627000000', '21628000000', '21629000000', '21630000000', '21631000000', '21632000000', '21633000000', '21634000000', '21635000000', '21636000000', '21637000000', '21638000000', '21639000000', '21640000000', '21641000000', '21642000000', '21643000000', '21644000000', '21645000000', '21646000000', '21647000000', '21648000000', '21649000000', '21650000000', '21651000000']);

test('is valid with the local key', function () {
    expect(TNPhone::make('20000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(TNPhone::make('21620000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(TNPhone::make('+21620000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(TNPhone::make('0021620000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(TNPhone::make('21620000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(TNPhone::make('21651000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = TNPhone::make('216 2-0000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('21620000000');
});

test('is not valid when too short', function () {
    expect(TNPhone::make('2000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(TNPhone::make('510000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(TNPhone::make('99920000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(TNPhone::make('00000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(TNPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(TNPhone::make('20000000')->all())->toEqual(['+21620000000', '0021620000000', '21620000000']);
});

test('toArray mirrors all', function () {
    $phone = TNPhone::make('20000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = TNPhone::make('21620000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('20000000');
});

test('config exposes the country schema', function () {
    $phone = TNPhone::make('20000000');
    expect($phone->config('key'))->toEqual('216')
        ->and($phone->config('code'))->toEqual('TN')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(TNPhone::make('216 2-0000000')->number())->toEqual('216 2-0000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = TNPhone::make('20000000');
    expect($phone->withPlus()->toString())->toEqual('+21620000000')
        ->and($phone->withoutPlus()->toString())->toEqual('21620000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(TNPhone::make('20000000')->toString())->toEqual('+21620000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '20000000'], ['phone' => TNPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => TNPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(TNPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '2000000'], ['phone' => TNPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '2000000'], ['phone' => TNPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '20000000'], ['phone' => TNPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '2000000'], ['phone' => TNPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = TNPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(TNPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('TN');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(TNPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('TN')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(TNPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
