<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\Commands\GeneratePhonesDatasetCommand;
use Workbench\App\Console\Commands\PhoneBenchmarkCommand;
use Workbench\App\Console\Commands\VerifyPhonesDatasetCommand;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GeneratePhonesDatasetCommand::class,
                VerifyPhonesDatasetCommand::class,
                PhoneBenchmarkCommand::class,
            ]);
        }
    }
}
