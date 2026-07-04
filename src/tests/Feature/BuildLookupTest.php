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
    $twelve = $lookup['byLength'][12];

    expect($lookup)->toHaveKey('byLength')
        ->and(array_keys($lookup['byLength']))->toBe([12])
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
    $leaf = (require $this->target)['byLength'][11][1]['$'];

    // two distinct patterns under +1: the shared US/CA one, and PR's own
    expect($leaf)->toHaveCount(2)
        ->and($leaf[0])->toEqual(['/^(?<provider>\d{3})(?<digits>\d{7})$/', [[0, 'US'], [1, 'CA']]])
        ->and($leaf[1])->toEqual(['/^(?<provider>787|939)(?<digits>\d{7})$/', [[2, 'PR']]]);
});

test('places a variable-length pattern in every length bucket it can produce', function () {
    config()->set('phones', [
        // key 20 (2) + provider 2 + digits {6,7} => totals 10 and 11
        'YY' => ['code' => 'YY', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>9\d)(?<digits>\d{6,7})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    $byLength = (require $this->target)['byLength'];

    expect(array_keys($byLength))->toBe([10, 11])
        ->and($byLength[10][2][0]['$'][0][1])->toEqual([[0, 'YY']])
        ->and($byLength[11][2][0]['$'][0][1])->toEqual([[0, 'YY']]);
});

test('skips entries missing a key or pattern', function () {
    config()->set('phones', [
        'XX' => ['code' => 'XX'],
        'EG' => ['code' => 'EG', 'key' => '20', 'local_key' => '0', 'pattern' => '(?<provider>1[0125])(?<digits>\d{8})'],
    ]);

    $this->artisan('phones:build-lookup', ['--path' => $this->target])->assertSuccessful();

    $byLength = (require $this->target)['byLength'];

    // only EG made it in: one length bucket (12), dialing code 2 -> 0
    expect(array_keys($byLength))->toBe([12])
        ->and($byLength[12][2][0])->toHaveKey('$');
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
