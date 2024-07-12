<?php

namespace MMAE\Phones;

use Illuminate\Support\ServiceProvider;

class MMAEPhonesServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }
    public function register(): void
    {
        $this->publishes([
            __DIR__."/../config/phones.php" => config_path('phones.php')
        ],'mmae::phones');

        $this->mergeConfigFrom(
            __DIR__.'/../config/phones.php', 'phones'
        );
    }

}
