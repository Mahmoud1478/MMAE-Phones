<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->target = sys_get_temp_dir().'/phones-lookup-'.uniqid().'/phone-lookup.php';
});

afterEach(function () {
    File::deleteDirectory(dirname($this->target));
});

test('compiles the schema into a length-first index', function () {
    config()->set('phones', [
        'EG' => ['code' => 'EG', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>1[0125])(?<digits>\d{8})'],
        'SA' => ['code' => 'SA', 'key' => '966', 'local_key' => '0', 'pattern' => '(?<provider>5)(?<digits>\d{8})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    expect(File::exists($this->target))->toBeTrue();

    $lookup = require $this->target;
    // EG: key 20 + 10 subscriber digits = total 12. SA: key 966 + 9 = total 12.
    // Both live under length 12; digit keys are ints (numeric-string keys cast).
    $twelve = $lookup['index'][12];

    expect($lookup)->toHaveKey('index')
        ->and(array_keys($lookup['index']))->toBe([12])
        ->and($twelve[2][0]['$'][0])->toEqual(['/^(?<provider>1[0125])(?<digits>\d{8})$/', [[0, 'EG']]])
        ->and($twelve[9][6][6]['$'][0])->toEqual(['/^(?<provider>5)(?<digits>\d{8})$/', [[1, 'SA']]]);
});

test('collapses countries sharing an identical pattern into one regex entry', function () {
    config()->set('phones', [
        'US' => ['code' => 'US', 'key' => '1', 'local_key' => '1', 'pattern' => '(?<provider>\d{3})(?<digits>\d{7})'],
        'CA' => ['code' => 'CA', 'key' => '1', 'local_key' => '1', 'pattern' => '(?<provider>\d{3})(?<digits>\d{7})'],
        'PR' => ['code' => 'PR', 'key' => '1', 'local_key' => '1', 'pattern' => '(?<provider>787|939)(?<digits>\d{7})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    // key 1 + 10 subscriber digits = total 11
    $leaf = (require $this->target)['index'][11][1];

    // The shared +1 code splits: the non-literal US/CA pattern stays a regex
    // bucket in `$`; PR's literal 787/939 provider moves into the `#` fixed-width
    // map (width 3), so detect() resolves it with a hash lookup, not a preg_match.
    expect($leaf['$'])->toHaveCount(1)
        ->and($leaf['$'][0])->toEqual(['/^(?<provider>\d{3})(?<digits>\d{7})$/', [[0, 'US'], [1, 'CA']]])
        ->and($leaf['#'])->toEqual([3 => ['787' => [[2, 'PR']], '939' => [[2, 'PR']]]]);
});

test('keeps a unique dialing code as a single regex bucket', function () {
    // A code with only one pattern never pays a per-country loop, so it is left
    // in `$` untouched even when its provider is a bare literal (no `#` map).
    config()->set('phones', [
        'SA' => ['code' => 'SA', 'key' => '966', 'local_key' => '0', 'pattern' => '(?<provider>5)(?<digits>\d{8})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    $leaf = (require $this->target)['index'][12][9][6][6];

    expect($leaf)->toHaveKey('$')
        ->and($leaf)->not->toHaveKey('#')
        ->and($leaf['$'][0])->toEqual(['/^(?<provider>5)(?<digits>\d{8})$/', [[0, 'SA']]]);
});

test('places a variable-length pattern in every length bucket it can produce', function () {
    config()->set('phones', [
        // key 20 (2) + provider 2 + digits {6,7} => totals 10 and 11
        'YY' => ['code' => 'YY', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>9\d)(?<digits>\d{6,7})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    $index = (require $this->target)['index'];

    expect(array_keys($index))->toBe([10, 11])
        ->and($index[10][2][0]['$'][0][1])->toEqual([[0, 'YY']])
        ->and($index[11][2][0]['$'][0][1])->toEqual([[0, 'YY']]);
});

test('skips entries missing a key or pattern', function () {
    config()->set('phones', [
        'XX' => ['code' => 'XX'],
        'EG' => ['code' => 'EG', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>1[0125])(?<digits>\d{8})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    $index = (require $this->target)['index'];

    // only EG made it in: one length bucket (12), dialing code 2 -> 0
    expect(array_keys($index))->toBe([12])
        ->and($index[12][2][0])->toHaveKey('$');
});

test('produces a syntactically valid php file', function () {
    config()->set('phones', ['EG' => ['code' => 'EG', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>1[0125])(?<digits>\d{8})']]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    exec('php -l '.escapeshellarg($this->target), $output, $status);

    expect($status)->toBe(0);
});

test('fails when no usable schema is configured', function () {
    config()->set('phones', ['XX' => ['code' => 'XX']]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertFailed();

    expect(File::exists($this->target))->toBeFalse();
});
