<?php

declare(strict_types=1);

namespace Hitech\DDDModularToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Hitech\DDDModularToolkit\Commands\MakeModifyMigration;
use Hitech\DDDModularToolkit\Commands\MakeModule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class DDDModularToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulesPath = App::path('Modules');

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

        $this->loadModuleRoutes();
        
        $this->loadMigrations();
    }

    protected function loadMigrations()
    {
        foreach (glob(App::path('Modules/*/Infrastructure/Database/Migrations'), GLOB_ONLYDIR) as $path) {
            $this->loadMigrationsFrom($path);
        }   
    }

    protected function loadModuleRoutes(): void
    {
        globRecursive(
            App::path('Modules/*/Interface/Routes/{web,api}.php'),
            function (string $routeFile) {
                require $routeFile;
            }
        );
    }
}
