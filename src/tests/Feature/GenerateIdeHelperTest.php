<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->target = sys_get_temp_dir().'/phones-meta-'.uniqid().'/phones.php';
});

afterEach(function () {
    File::deleteDirectory(dirname($this->target));
});

test('writes a meta file with every configured country code, sorted', function () {
    config()->set('phones', [
        'ZW' => ['code' => 'ZW'],
        'EG' => ['code' => 'EG'],
        'SA' => ['code' => 'SA'],
    ]);

    $this->artisan('phones:ide-helper', ['--path' => $this->target])
        ->assertSuccessful();

    expect(File::exists($this->target))->toBeTrue();

    $contents = File::get($this->target);

    expect($contents)
        ->toContain("'EG',")
        ->toContain("'SA',")
        ->toContain("'ZW',")
        ->toContain('namespace PHPSTORM_META')
        ->toContain("registerArgumentsSet(\n        'mmae_phones_country_codes',");

    // sorted: EG before SA before ZW
    expect(strpos($contents, "'EG',"))->toBeLessThan(strpos($contents, "'SA',"));
    expect(strpos($contents, "'SA',"))->toBeLessThan(strpos($contents, "'ZW',"));
});

test('registers autocomplete for every generic entry point', function () {
    config()->set('phones', ['EG' => ['code' => 'EG']]);

    $this->artisan('phones:ide-helper', ['--path' => $this->target])
        ->assertSuccessful();

    expect(File::get($this->target))
        ->toContain('expectedArguments(\MMAE\Phones\Phone::make(), 1, argumentsSet(\'mmae_phones_country_codes\'))')
        ->toContain('expectedArguments(\MMAE\Phones\Base\BasePhone::for(), 0, argumentsSet(\'mmae_phones_country_codes\'))')
        ->toContain('expectedArguments(\MMAE\Phones\Rules\PhoneRule::make(), 0, argumentsSet(\'mmae_phones_country_codes\'))')
        ->toContain('expectedArguments(\MMAE\Phones\Placeholders\Placeholder::make(), 0, argumentsSet(\'mmae_phones_country_codes\'))');
});

test('produces a syntactically valid php file', function () {
    config()->set('phones', ['EG' => ['code' => 'EG'], 'SA' => ['code' => 'SA']]);

    $this->artisan('phones:ide-helper', ['--path' => $this->target])
        ->assertSuccessful();

    exec('php -l '.escapeshellarg($this->target), $output, $status);

    expect($status)->toBe(0);
});

test('fails when no country codes are configured', function () {
    config()->set('phones', []);

    $this->artisan('phones:ide-helper', ['--path' => $this->target])
        ->assertFailed();

    expect(File::exists($this->target))->toBeFalse();
});
