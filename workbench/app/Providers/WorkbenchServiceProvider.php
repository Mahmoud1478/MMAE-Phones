<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\Commands\GeneratePhonesDatasetCommand;
use Workbench\App\Console\Commands\PhoneBenchmarkCommand;
use Workbench\App\Console\Commands\VerifyPhonesDatasetCommand;

use function Orchestra\Testbench\workbench_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Point Livewire's single-file component discovery at the workbench tree
        // instead of Testbench's throwaway skeleton (the default resource_path()).
        // Deferred to booted() so it runs after Livewire's own path registration,
        // regardless of provider boot order.
        $this->app->booted(fn () => $this->registerWorkbenchLivewireComponents());

        if ($this->app->runningInConsole()) {
            $this->commands([
                GeneratePhonesDatasetCommand::class,
                VerifyPhonesDatasetCommand::class,
                PhoneBenchmarkCommand::class,
            ]);
        }
    }

    /**
     * Register the workbench SFC locations and `pages::` / `layouts::` namespaces,
     * mirroring how Livewire registers them from config in its own boot.
     */
    private function registerWorkbenchLivewireComponents(): void
    {
        foreach ([
            workbench_path('resources/views/components'),
            workbench_path('resources/views/livewire'),
        ] as $location) {
            app('livewire.finder')->addLocation(viewPath: $location);

            if (! is_dir($location)) {
                continue;
            }

            app('blade.compiler')->anonymousComponentPath($location);
            app('view')->addLocation($location);
        }

        foreach ([
            'layouts' => workbench_path('resources/views/layouts'),
            'pages' => workbench_path('resources/views/pages'),
        ] as $namespace => $location) {
            app('livewire.finder')->addNamespace($namespace, viewPath: $location);

            if (! is_dir($location)) {
                continue;
            }

            app('blade.compiler')->anonymousComponentPath($location, $namespace);
            app('view')->addNamespace($namespace, $location);
        }
    }
}
