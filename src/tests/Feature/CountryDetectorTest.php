<?php

use MMAE\Phones\CountryDetector;

afterEach(fn () => CountryDetector::flush());

test('detects a single country from a plus-prefixed international number', function () {
    expect(CountryDetector::detect('+201000000000'))->toEqual(['EG']);
});

test('detects the same country from every international prefix shape', function (string $number) {
    expect(CountryDetector::detect($number))->toContain('EG');
})->with([
    '+201000000000',   // plus
    '00201000000000',  // double zero
    '201000000000',    // bare dialing code
]);

test('strips spaces and dashes before detecting', function () {
    expect(CountryDetector::detect('+20 100-0000000'))->toEqual(['EG']);
});

test('returns every country sharing a dialing code', function () {
    $codes = CountryDetector::detect('+15551234567');

    // +1 is the NANP: the US and its territories all share it
    expect($codes)->toContain('US')
        ->and($codes)->toContain('CA')
        ->and(count($codes))->toBeGreaterThan(1);
});

test('returns an empty list for a local number carrying no dialing code', function () {
    expect(CountryDetector::detect('01000000000'))->toEqual([]);
});

test('returns an empty list when nothing matches', function () {
    expect(CountryDetector::detect('+99900000'))->toEqual([]);
});

test('returns an empty list for empty or non-numeric input', function (string $number) {
    expect(CountryDetector::detect($number))->toEqual([]);
})->with(['', 'abcdef']);

test('detectFirst returns the first matching code', function () {
    expect(CountryDetector::detectFirst('+201000000000'))->toEqual('EG');
});

test('detectFirst returns null when nothing matches', function () {
    expect(CountryDetector::detectFirst('01000000000'))->toBeNull();
});

test('prefers the precompiled lookup over runtime config(phones)', function () {
    // the shipped lookup knows EG; wiping the schema proves the compiled
    // lookup — not config('phones') — is what drives detection when present
    config()->set('phones', []);
    CountryDetector::flush();

    expect(CountryDetector::detect('+201000000000'))->toEqual(['EG']);
});

test('throws an actionable error when no compiled lookup is present', function () {
    config()->set('phone-lookup', null);
    CountryDetector::flush();

    expect(fn () => CountryDetector::detect('+201000000000'))
        ->toThrow(RuntimeException::class, 'phones:build-lookup');
});

test('flush forces a reload of the lookup after config changes', function () {
    expect(CountryDetector::detect('+201000000000'))->toEqual(['EG']);

    // swap in a different baked index; detection only picks it up after flush
    config()->set('phone-lookup', ['index' => []]);
    CountryDetector::flush();

    expect(CountryDetector::detect('+201000000000'))->toEqual([]);
});
