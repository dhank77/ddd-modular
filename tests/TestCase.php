<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Hitech\DDDModularToolkit\Providers\DDDModularToolkitServiceProvider;
use Hitech\DDDModularToolkit\Commands\MakeModule;
use Hitech\DDDModularToolkit\Commands\MakeModifyMigration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure modules directory exists
        $modulesPath = App::path('Modules');
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }
        
        // Manually register commands for testing
        $this->artisan('list'); // This forces command registration
        
        // Setup test environment
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            DDDModularToolkitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        
        // Set up DDD config
        $app['config']->set('ddd.blade.is_active', true);
        $app['config']->set('ddd.blade.path', 'Blade');
        $app['config']->set('ddd.react.is_active', false);
        $app['config']->set('ddd.react.path', 'React');
        $app['config']->set('ddd.middleware.auth', true);
        $app['config']->set('ddd.middleware.api', true);
    }

    protected function setUpDatabase()
    {
        // Create basic Laravel tables if needed
    }
    
    protected function tearDown(): void
    {
        // Clean up test modules
        $modulesPath = App::path('Modules');
        if (File::exists($modulesPath)) {
            $directories = File::directories($modulesPath);
            foreach ($directories as $directory) {
                if (str_contains($directory, 'Test')) {
                    File::deleteDirectory($directory);
                }
            }
        }
        
        parent::tearDown();
    }
    
    /**
     * Define additional package providers.
     */
    protected function getPackageAliases($app)
    {
        return [];
    }
    
    /**
     * Resolve application Console Kernel implementation.
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Console\Kernel', function ($app) {
            $kernel = new \Illuminate\Foundation\Console\Kernel($app, $app['events']);
            
            // Force register our commands
            $kernel->registerCommand(new MakeModule());
            $kernel->registerCommand(new MakeModifyMigration());
            
            return $kernel;
        });
    }
}