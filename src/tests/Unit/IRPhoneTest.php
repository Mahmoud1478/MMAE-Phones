<?php

use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Phones\IRPhone;
use MMAE\Phones\Placeholders\IRPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;
use MMAE\Phones\Rules\IRPhoneRule;

test('can create a phone object', function () {
    expect(IRPhone::make('09000000000'))->toBeInstanceOf(IRPhone::class);
});

test('validates every provider variant in international form', function (string $number) {
    expect(IRPhone::make($number)->isValid())->toBeTrue();
})->with(['989000000000', '989100000000', '989200000000', '989300000000', '989400000000', '989500000000', '989600000000', '989700000000', '989800000000', '989900000000']);

test('is valid with the local key', function () {
    expect(IRPhone::make('09000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and no plus', function () {
    expect(IRPhone::make('989000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and a plus', function () {
    expect(IRPhone::make('+989000000000')->isValid())->toBeTrue();
});

test('is valid with the country key and double zeros', function () {
    expect(IRPhone::make('00989000000000')->isValid())->toBeTrue();
});

test('is valid at the minimum digit length', function () {
    expect(IRPhone::make('989000000000')->isValid())->toBeTrue();
});

test('is valid at the maximum digit length', function () {
    expect(IRPhone::make('989900000000')->isValid())->toBeTrue();
});

test('strips spaces and dashes before validating', function () {
    $phone = IRPhone::make('98 9-000000000');
    expect($phone->isValid())->toBeTrue()
        ->and($phone->toString())->toEqual('989000000000');
});

test('is not valid when too short', function () {
    expect(IRPhone::make('900000000')->isNotValid())->toBeTrue();
});

test('is not valid when too long', function () {
    expect(IRPhone::make('99000000000')->isNotValid())->toBeTrue();
});

test('is not valid with a wrong country key', function () {
    expect(IRPhone::make('9999000000000')->isNotValid())->toBeTrue();
});

test('is not valid because of the starter number', function () {
    expect(IRPhone::make('00000000000')->isNotValid())->toBeTrue();
});

test('is not valid for empty or non-numeric input', function (string $number) {
    expect(IRPhone::make($number)->isNotValid())->toBeTrue();
})->with(['', 'abcdefghij']);

test('lists every accepted key-prefixed shape', function () {
    expect(IRPhone::make('09000000000')->all())->toEqual(['+989000000000', '00989000000000', '989000000000', '09000000000']);
});

test('toArray mirrors all', function () {
    $phone = IRPhone::make('09000000000');
    expect($phone->toArray())->toEqual($phone->all());
});

test('segments expose the provider and digits groups', function () {
    $segments = IRPhone::make('989000000000')->segments();
    expect($segments)->toHaveKeys(['provider', 'digits'])
        ->and($segments['provider'].$segments['digits'])->toEqual('9000000000');
});

test('config exposes the country schema', function () {
    $phone = IRPhone::make('09000000000');
    expect($phone->config('key'))->toEqual('98')
        ->and($phone->config('code'))->toEqual('IR')
        ->and($phone->config())->toBeArray();
});

test('number returns the raw input untouched', function () {
    expect(IRPhone::make('98 9-000000000')->number())->toEqual('98 9-000000000');
});

test('withPlus and withoutPlus toggle the plus prefix', function () {
    $phone = IRPhone::make('09000000000');
    expect($phone->withPlus()->toString())->toEqual('+989000000000')
        ->and($phone->withoutPlus()->toString())->toEqual('989000000000');
})->after(fn () => BasePhone::$plus = false);

test('the static plus flag affects the string form', function () {
    BasePhone::$plus = true;
    expect(IRPhone::make('09000000000')->toString())->toEqual('+989000000000');
})->after(fn () => BasePhone::$plus = false);

test('rule passes validation for a valid number', function () {
    $validator = Validator::make(['phone' => '09000000000'], ['phone' => IRPhoneRule::make()]);
    expect($validator->passes())->toBeTrue();
});

test('rule fails validation for an invalid number', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => IRPhoneRule::make()]);
    expect($validator->fails())->toBeTrue();
});

test('rule locks the locale and exposes no code setter', function () {
    expect(method_exists(IRPhoneRule::class, 'for'))->toBeFalse();
    $validator = Validator::make(['phone' => '900000000'], ['phone' => IRPhoneRule::make('SA')]);
    expect($validator->fails())->toBeTrue();
});

test('rule callback takes full control of the flow', function () {
    $pass = Validator::make(['phone' => '900000000'], ['phone' => IRPhoneRule::make()->validateUsing(fn () => null)]);
    expect($pass->passes())->toBeTrue();

    $fail = Validator::make(['phone' => '09000000000'], ['phone' => IRPhoneRule::make()->validateUsing(fn ($phone, $attribute, $value, $config, $fail) => $fail('nope'))]);
    expect($fail->fails())->toBeTrue();
});

test('rule message can be overridden', function () {
    $validator = Validator::make(['phone' => '900000000'], ['phone' => IRPhoneRule::make()->message('bad phone')]);
    expect($validator->errors()->first('phone'))->toEqual('bad phone');
});

test('placeholder is locked to the country code', function () {
    $placeholder = IRPlaceholder::make();
    expect($placeholder)->toBeInstanceOf(IRPlaceholder::class)
        ->and($placeholder->extract()->code)->toEqual('IR');
});

test('placeholder mirrors the generic placeholder for its code', function () {
    expect(IRPlaceholder::make()->extract()->toArray())
        ->toEqual(Placeholder::make('IR')->extract()->toArray());
});

test('placeholder mask flows through to the extracted data', function () {
    expect(IRPlaceholder::make('#')->extract()->mask)->toEqual('#');
});
