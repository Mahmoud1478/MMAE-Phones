<?php

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\CountryDetector;
use MMAE\Phones\Phone;
use Workbench\App\Support\RegexSampler;

/**
 * Real-world scenario: importing a large list of owners, each carrying several
 * phone numbers in international form only (a dialing key, no ISO country), and
 * validating every number *before* anything is saved.
 *
 * The country is recovered from the dialing key with {@see CountryDetector},
 * then the full number is validated/normalized with {@see Phone}.
 */
afterEach(function () {
    CountryDetector::flush();
    BasePhone::$plus = false;
});

/**
 * The import pass from the README recipe: detect the country from the key,
 * validate/normalize, and split rows into valid (canonical +CC numbers) and
 * failed (name + offending raw number).
 *
 * @param  iterable<array{name: string, phones: list<string>}>  $owners
 * @return array{valid: list<array{name: string, phones: list<string>}>, failed: list<array{name: string, phone: string}>}
 */
function runImport(iterable $owners): array
{
    $valid = [];
    $failed = [];

    foreach ($owners as $owner) {
        $normalized = [];

        foreach ($owner['phones'] as $raw) {
            $code = CountryDetector::detectFirst($raw);
            $phone = $code ? Phone::make($raw, $code) : null;

            if ($phone === null || $phone->isNotValid()) {
                $failed[] = ['name' => $owner['name'], 'phone' => $raw];

                continue;
            }

            $normalized[] = $phone->withPlus()->toString();
        }

        if ($normalized !== []) {
            $valid[] = ['name' => $owner['name'], 'phones' => $normalized];
        }
    }

    return ['valid' => $valid, 'failed' => $failed];
}

/**
 * A random valid international number ('+' + key + subscriber) for a country.
 */
function intlNumber(string $code, string $prefix = '+'): string
{
    /** @var array{key: string, pattern: string} $schema */
    $schema = config("phones.$code");

    return $prefix.$schema['key'].(new RegexSampler($schema['pattern']))->sample();
}

test('detects the country from the key and validates every number before saving', function () {
    $owners = [
        ['name' => 'Owner EG', 'phones' => [intlNumber('EG'), intlNumber('EG', '00')]],
        ['name' => 'Owner SA', 'phones' => [intlNumber('SA')]],
        ['name' => 'Owner AE', 'phones' => [intlNumber('AE', '')]],
    ];

    $result = runImport($owners);

    expect($result['failed'])->toBe([])
        ->and($result['valid'])->toHaveCount(3);

    foreach ($result['valid'] as $owner) {
        foreach ($owner['phones'] as $phone) {
            // every stored number is the canonical +CC form
            expect($phone)->toStartWith('+');
        }
    }
});

test('collects the invalid numbers instead of saving them', function () {
    $owners = [
        ['name' => 'Half valid', 'phones' => [
            intlNumber('EG'),   // ok
            '01000000000',      // local form — no dialing code, detect() => []
            '+201',             // right key, too short
            'not-a-number',     // garbage
        ]],
        ['name' => 'All bad', 'phones' => ['+99900000', '000']],
    ];

    $result = runImport($owners);

    // the one good number is kept; its owner survives with only that number
    expect($result['valid'])->toHaveCount(1)
        ->and($result['valid'][0]['name'])->toBe('Half valid')
        ->and($result['valid'][0]['phones'])->toHaveCount(1);

    // every bad number is reported, none silently dropped
    $failedNumbers = array_column($result['failed'], 'phone');
    expect($failedNumbers)->toContain('01000000000', '+201', 'not-a-number', '+99900000', '000')
        ->and($result['failed'])->toHaveCount(5);
});

test('a local-form number carries no country and is rejected on import', function () {
    // detection is international-only: a trunk-0 number resolves to no country,
    // so the import can never mis-save it under a guessed locale
    expect(CountryDetector::detect('01000000000'))->toBe([]);

    $result = runImport([['name' => 'Local only', 'phones' => ['01000000000']]]);

    expect($result['valid'])->toBe([])
        ->and($result['failed'])->toHaveCount(1);
});

test('validates a large batch of mixed numbers correctly', function () {
    $codes = ['EG', 'SA', 'AE', 'LY', 'KW', 'BH', 'QA'];
    $owners = [];
    $expectedValid = 0;
    $expectedFailed = 0;

    // 1,000 owners, each with a few valid international numbers and one bad one
    for ($i = 0; $i < 1000; $i++) {
        $code = $codes[$i % count($codes)];
        // local trunk-0 form: no dialing code, so detect() => [] and it is rejected
        $phones = [intlNumber($code), intlNumber($code, '00'), '01000000000'];
        $owners[] = ['name' => "Owner $i", 'phones' => $phones];
        $expectedValid += 2;
        $expectedFailed += 1;
    }

    $result = runImport($owners);

    $validCount = array_sum(array_map(fn ($o) => count($o['phones']), $result['valid']));

    expect($validCount)->toBe($expectedValid)
        ->and($result['failed'])->toHaveCount($expectedFailed)
        ->and($result['valid'])->toHaveCount(1000);
});
