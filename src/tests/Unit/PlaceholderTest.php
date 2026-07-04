<?php

use MMAE\Phones\Configs\PlaceholderData;
use MMAE\Phones\Placeholders\EGPlaceholder;
use MMAE\Phones\Placeholders\Placeholder;

test('extract returns a PlaceholderData', function () {
    expect(EGPlaceholder::make()->extract())->toBeInstanceOf(PlaceholderData::class);
});

test('enumerates concrete provider prefixes from the schema', function () {
    expect(EGPlaceholder::make()->extract()->providers)->toEqual(['10', '11', '12', '15']);
});

test('expands digit ranges inside a class', function () {
    // LY provider 9[1-5]
    expect(Placeholder::make('LY')->extract()->providers)->toEqual(['91', '92', '93', '94', '95']);
});

test('masks open wildcard digits in the provider', function () {
    // QA provider [5|6|3|7]\d{1}
    expect(Placeholder::make('QA')->extract()->providers)->toEqual(['3X', '5X', '6X', '7X']);
});

test('reports the subscriber digit length', function () {
    $data = EGPlaceholder::make()->extract();
    expect($data->digitsMin)->toEqual(8)
        ->and($data->digitsMax)->toEqual(8)
        ->and($data->digitsMask())->toEqual('XXXXXXXX');
});

test('carries the country key and local key', function () {
    $data = EGPlaceholder::make()->extract();
    expect($data->code)->toEqual('EG')
        ->and($data->key)->toEqual('20')
        ->and($data->localKey)->toEqual('0');
});

test('builds bare, local and international example shapes', function () {
    $data = EGPlaceholder::make()->extract();
    expect($data->bare())->toEqual('10XXXXXXXX')
        ->and($data->local())->toEqual('010XXXXXXXX')
        ->and($data->international())->toEqual('+2010XXXXXXXX')
        ->and($data->international(false))->toEqual('2010XXXXXXXX');
});

test('example shapes accept a specific provider', function () {
    $data = EGPlaceholder::make()->extract();
    expect($data->local('15'))->toEqual('015XXXXXXXX')
        ->and($data->international(true, '15'))->toEqual('+2015XXXXXXXX');
});

test('examples lists every shape per provider', function () {
    $examples = EGPlaceholder::make()->extract()->examples();
    expect($examples)->toHaveCount(4)
        ->and($examples[0])->toEqual([
            'provider' => '10',
            'bare' => '10XXXXXXXX',
            'local' => '010XXXXXXXX',
            'international' => '+2010XXXXXXXX',
        ]);
});

test('toArray exposes the detailed structure', function () {
    $array = EGPlaceholder::make()->extract()->toArray();
    expect($array)->toMatchArray([
        'code' => 'EG',
        'key' => '20',
        'local_key' => '0',
        'providers' => ['10', '11', '12', '15'],
        'digits' => ['min' => 8, 'max' => 8, 'mask' => 'XXXXXXXX'],
        'example' => [
            'bare' => '10XXXXXXXX',
            'local' => '010XXXXXXXX',
            'international' => '+2010XXXXXXXX',
        ],
    ]);
});

test('collapses every provider into one compact format token', function () {
    $data = EGPlaceholder::make()->extract();
    expect($data->providerMask())->toEqual('1[0,1,2,5]')
        ->and($data->bareFormat())->toEqual('1[0,1,2,5]XXXXXXXX')
        ->and($data->localFormat())->toEqual('01[0,1,2,5]XXXXXXXX')
        ->and($data->internationalFormat())->toEqual('+201[0,1,2,5]XXXXXXXX');
});

test('brackets the first column when the leading digit varies', function () {
    // QA providers 3X, 5X, 6X, 7X
    expect(Placeholder::make('QA')->extract()->providerMask())->toEqual('[3,5,6,7]X');
});

test('generic placeholder requires a country code', function () {
    Placeholder::make('');
})->throws(InvalidArgumentException::class);
