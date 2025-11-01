<?php

declare(strict_types=1);

namespace Hitech\DDDModularToolkit\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Hitech\DDDModularToolkit\Commands\MakeModule;
use Hitech\DDDModularToolkit\Commands\MakeModifyMigration;
use Illuminate\Support\Facades\Route;

class DDDModularToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulesPath = App::path('Modules');

        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }

        // Dynamic repository bindings for all modules
        $this->bindModuleRepositories();
    }

    public function boot(): void
    {
        // Always register commands in console or testing environment
        if ($this->app->runningInConsole() || $this->app->environment('testing') || app()->environment('testing')) {
            $this->commands([
                MakeModule::class,
                MakeModifyMigration::class,
            ]);
        }

        // Create modules directory if it doesn't exist
        $modulesPath = App::path('Modules');
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
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

    /**
     * Dynamically bind all module repositories to their interfaces.
     */
    private function bindModuleRepositories(): void
    {
        $modulesPath = App::path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $moduleDir) {
            $moduleName = basename($moduleDir);
            $this->bindRepositoriesForModule($moduleDir, $moduleName);
        }
    }

    /**
     * Bind repositories for a specific module.
     */
    private function bindRepositoriesForModule(string $moduleDir, string $moduleName): void
    {
        $contractsPath = "{$moduleDir}/Domain/Contracts";
        $repositoriesPath = "{$moduleDir}/Infrastructure/Repositories";

        if (! File::exists($contractsPath) || ! File::exists($repositoriesPath)) {
            return;
        }

        $this->bindContractsInDirectory($contractsPath, $repositoriesPath, $moduleName);
    }

    /**
     * Bind contracts in a directory (including subdirectories).
     */
    private function bindContractsInDirectory(string $contractsPath, string $repositoriesPath, string $moduleName): void
    {
        if (! File::exists($contractsPath)) {
            return;
        }

        // Get all contract files recursively
        $contractFiles = File::allFiles($contractsPath);
        
        foreach ($contractFiles as $contractFile) {
            // Get the relative path from the contracts directory
            $relativePath = str_replace($contractsPath . '/', '', $contractFile->getPathname());
            $relativePath = str_replace('.php', '', $relativePath);

            // Get the interface name (filename without extension)
            $interfaceName = pathinfo($contractFile->getFilename(), PATHINFO_FILENAME);

            // Get subdirectory path for namespace
            $subDir = dirname($relativePath);
            $subNamespace = ($subDir !== '.' && $subDir !== '') ? '\\' . str_replace('/', '\\', $subDir) : '';

            // Build the full interface class name
            $interfaceClass = "App\\Modules\\{$moduleName}\\Domain\\Contracts{$subNamespace}\\{$interfaceName}";
            
            // Build the repository class name with the same subdirectory structure
            $repositoryClass = "App\\Modules\\{$moduleName}\\Infrastructure\\Repositories{$subNamespace}\\{$interfaceName}";

            // Remove 'Interface' suffix from repository class name if it exists
            if (str_ends_with($repositoryClass, 'Interface')) {
                $repositoryClass = substr($repositoryClass, 0, -9);
            }

            if (interface_exists($interfaceClass) && class_exists($repositoryClass)) {
                $this->app->bind($interfaceClass, $repositoryClass);
            }
        }
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
