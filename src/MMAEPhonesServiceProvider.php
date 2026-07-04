<?php

declare(strict_types=1);

namespace MMAE\Phones;

use Illuminate\Support\ServiceProvider;
use MMAE\Phones\Commands\BuildLookupCommand;
use MMAE\Phones\Commands\GenerateIdeHelperCommand;

/**
 * Service provider for the phones package.
 *
 * Merges `config/phones.php` into the app config, loads the `phones`
 * translation namespace (`lang/{locale}/validation.php`), registers both for
 * publishing under the `mmae::phones` (config) and `mmae::phones-lang`
 * (translations) tags, and registers the `phones:ide-helper` command.
 */
class MMAEPhonesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'phones');

        $this->publishes([
            __DIR__.'/../config/phones.php' => config_path('phones.php'),
            __DIR__.'/../config/phone-lookup.php' => config_path('phone-lookup.php'),
        ], 'mmae::phones');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('phones'),
        ], 'mmae::phones-lang');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateIdeHelperCommand::class,
                BuildLookupCommand::class,
            ]);
        }
    }

    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/phones.php', 'phones'
        );

        // Guard a deleted lookup so boot survives; detect() surfaces the failure.
        $lookup = __DIR__.'/../config/phone-lookup.php';
        if (file_exists($lookup)) {
            $this->mergeConfigFrom($lookup, 'phone-lookup');
        }
    }
}
