<?php

declare(strict_types=1);

namespace Hitech\DDDModularToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Hitech\DDDModularToolkit\Commands\MakeModifyMigration;
use Hitech\DDDModularToolkit\Commands\MakeModule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class DDDModularToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulesPath = App::path('Modules');

        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }

        foreach (File::directories($modulesPath) as $moduleDir) {
            $moduleName = basename($moduleDir);

            $contractNamespace = "App\\Modules\\{$moduleName}\\Domain\\Contracts";
            $repositoryNamespace = "App\\Modules\\{$moduleName}\\Infrastructure\\Repositories";

            if (File::exists("{$moduleDir}/Domain/Contracts") && File::exists("{$moduleDir}/Infrastructure/Repositories")) {
                foreach (File::files("{$moduleDir}/Domain/Contracts") as $contractFile) {
                    $interfaceName = pathinfo($contractFile->getFilename(), PATHINFO_FILENAME);
                    $interfaceClass = "{$contractNamespace}\\{$interfaceName}";

                    $repositoryClass = "{$repositoryNamespace}\\{$interfaceName}";

                    if (str_ends_with($repositoryClass, 'Interface')) {
                        $repositoryClass = substr($repositoryClass, 0, -9);
                    }

                    if (interface_exists($interfaceClass) && class_exists($repositoryClass)) {
                        $this->app->bind($interfaceClass, $repositoryClass);
                    }
                }
            }
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModule::class,
                MakeModifyMigration::class,
            ]);
        }

        $this->publishes(
            [
                __DIR__ . '/../../config/ddd.php' => App::configPath('ddd.php'),
            ],
            ['ddd', 'ddd-config']
        );

        $this->publishes([
            __DIR__ . '/../../config/ddd.php' => App::configPath('ddd.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../config/ddd.php', 'ddd');

        $this->loadModuleRoutes();

        $this->loadMigrations();

        $this->loadBlade();
    }

    protected function loadMigrations()
    {
        $modulesPath = App::path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        foreach (glob(App::path('Modules/*/Infrastructure/Database/Migrations'), GLOB_ONLYDIR) as $path) {
            $this->loadMigrationsFrom($path);
        }
    }

    protected function loadModuleRoutes(): void
    {
        $modulesPath = App::path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $middleWare = ['web'];

        if (config('ddd.middleware.auth')) {
            $middleWare[] = 'auth';
        }

        Route::middleware($middleWare)->group(function () {
            globRecursive(
                App::path('Modules/*/Interface/Routes/web.php'),
                function (string $routeFile) {
                    require $routeFile;
                }
            );
        });

        if (config('ddd.middleware.api')) {
            Route::middleware('api')->group(function () {
                globRecursive(
                    App::path('Modules/*/Interface/Routes/api.php'),
                    function (string $routeFile) {
                        require $routeFile;
                    }
                );
            });
        }
    }

    protected function loadBlade(): void
    {
        if (config('ddd.blade.is_active')) {
            $modulesPath = App::path('Modules');
            foreach (scandir($modulesPath) as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }

                $viewPath = $modulesPath . '/' . $module . '/Interface' . '/' . config('ddd.blade.path');
                if (is_dir($viewPath)) {
                    View::addNamespace($module, $viewPath);
                }
            }
        }
    }
}
