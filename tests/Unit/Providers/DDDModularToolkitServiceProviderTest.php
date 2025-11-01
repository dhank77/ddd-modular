<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Hitech\DDDModularToolkit\Commands\MakeModule;
use Hitech\DDDModularToolkit\Commands\MakeModifyMigration;
use Hitech\DDDModularToolkit\Providers\DDDModularToolkitServiceProvider;

class DDDModularToolkitServiceProviderTest extends TestCase
{
    /** @test */
    public function it_creates_modules_directory_if_not_exists()
    {
        $modulesPath = App::path('Modules');
        
        // The directory should exist after service provider boots
        $this->assertTrue(File::exists($modulesPath));
    }

    /** @test */
    public function it_registers_commands_when_running_in_console()
    {
        // Force the application to think it's running in console
        $this->app->instance('env', 'testing');
        
        // Get all registered commands
        $kernel = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $commands = $kernel->all();
        
        // Check if our commands are registered
        $this->assertTrue(isset($commands['make:module']), 'make:module command not found');
        $this->assertTrue(isset($commands['make:modify-migration']), 'make:modify-migration command not found');
    }

    /** @test */
    public function it_merges_config_from_package()
    {
        $this->assertNotNull(config('ddd.blade.is_active'));
        $this->assertNotNull(config('ddd.middleware.auth'));
        $this->assertTrue(config('ddd.blade.is_active'));
        $this->assertTrue(config('ddd.middleware.auth'));
    }

    /** @test */
    public function it_has_publishable_config_file()
    {
        // Test that the config file exists in the package
        $configPath = __DIR__ . '/../../../config/ddd.php';
        $this->assertTrue(File::exists($configPath));
        
        // Test that config is properly loaded
        $this->assertIsArray(config('ddd'));
        $this->assertArrayHasKey('blade', config('ddd'));
        $this->assertArrayHasKey('middleware', config('ddd'));
    }
    
    /** @test */
    public function it_can_bind_interfaces_to_implementations_with_subdirectories()
    {
        // Create a test module with subdirectories
        $modulesPath = App::path('Modules');
        $testModulePath = $modulesPath . '/TestModule';
        
        // Clean up if exists
        if (File::exists($testModulePath)) {
            File::deleteDirectory($testModulePath);
        }
        
        // Create directory structure
        $directories = [
            'TestModule/Domain/Contracts/Master',
            'TestModule/Infrastructure/Repositories/Master',
        ];
        
        foreach ($directories as $dir) {
            $fullPath = "{$modulesPath}/{$dir}";
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
        }
        
        // Create test interface
        File::put("{$modulesPath}/TestModule/Domain/Contracts/Master/TestRepositoryInterface.php", <<<'PHP'
<?php

namespace App\Modules\TestModule\Domain\Contracts\Master;

interface TestRepositoryInterface
{
    public function test();
}
PHP);
        
        // Create test implementation
        File::put("{$modulesPath}/TestModule/Infrastructure/Repositories/Master/TestRepository.php", <<<'PHP'
<?php

namespace App\Modules\TestModule\Infrastructure\Repositories\Master;

use App\Modules\TestModule\Domain\Contracts\Master\TestRepositoryInterface;

class TestRepository implements TestRepositoryInterface
{
    public function test()
    {
        return 'test';
    }
}
PHP);
        
        // Re-register the service provider to trigger binding
        $this->app->register(DDDModularToolkitServiceProvider::class, true);
        
        // Check if binding works
        $interfaceClass = "App\\Modules\\TestModule\\Domain\\Contracts\\Master\\TestRepositoryInterface";
        $repositoryClass = "App\\Modules\\TestModule\\Infrastructure\\Repositories\\Master\\TestRepository";
        
        $this->assertTrue($this->app->bound($interfaceClass));
        $this->assertInstanceOf($repositoryClass, $this->app->make($interfaceClass));
        
        // Clean up
        if (File::exists($testModulePath)) {
            File::deleteDirectory($testModulePath);
        }
    }
}