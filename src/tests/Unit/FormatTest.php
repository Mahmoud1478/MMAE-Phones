<?php

use MMAE\Phones\Phones\EGPhone;

test('format substitutes each named token', function (string $format, string $expected) {
    expect(EGPhone::make('01012345678')->format($format))->toEqual($expected);
})->with([
    'key' => ['{key}', '20'],
    'local' => ['{local}', '0'],
    'provider' => ['{provider}', '10'],
    'digits' => ['{digits}', '12345678'],
]);

test('format combines tokens with literal separators', function () {
    expect(EGPhone::make('01012345678')->format('+{key} {provider}-{digits}'))
        ->toEqual('+20 10-12345678');
});

test('format keeps the local key form', function () {
    expect(EGPhone::make('01012345678')->format('{local}{provider}{digits}'))
        ->toEqual('01012345678');
});

test('format leaves literal text untouched even when it contains token words', function () {
    expect(EGPhone::make('01012345678')->format('key: {key}'))
        ->toEqual('key: 20');
});

test('format leaves an unbraced token literally', function () {
    expect(EGPhone::make('01012345678')->format('key {key}'))
        ->toEqual('key 20');
});

test('format emits an unknown token literally', function () {
    expect(EGPhone::make('01012345678')->format('{unknown} {key}'))
        ->toEqual('{unknown} 20');
});

test('format returns an empty string when the number is invalid', function () {
    expect(EGPhone::make('100000000')->format('{key}-{provider}-{digits}'))
        ->toEqual('');
});

test('format works with the international input form', function () {
    expect(EGPhone::make('+201012345678')->format('{key} {provider} {digits}'))
        ->toEqual('20 10 12345678');
});
