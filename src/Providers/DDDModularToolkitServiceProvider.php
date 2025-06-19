<?php

declare(strict_types=1);

namespace Vendor\DDDModularToolkit\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Vendor\DDDModularToolkit\Commands\MakeModifyMigration;
use Vendor\DDDModularToolkit\Commands\MakeModule;

class DDDModularToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModule::class,
                MakeModifyMigration::class,
            ]);
        }

        $this->loadModuleRoutes();
    }

    protected function loadModuleRoutes(): void
    {
        globRecursive(__DIR__ . '/../app/Modules/*/Interface/Routes/web.php', function ($routeFile) {
            require $routeFile;
        });
        globRecursive(__DIR__ . '/../app/Modules/*/Interface/Routes/api.php', function ($routeFile) {
            require $routeFile;
        });
    }
}
