<?php

namespace MMAE\Phones\tests;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    protected $enablesPackageDiscoveries = true;
    protected function getPackageProviders($app): array
    {
        return [
            'MMAE\Phones\MMAEPhonesServiceProvider',
        ];
    }
}
