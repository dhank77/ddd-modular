<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class MakeModuleTest extends TestCase
{
    private string $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->modulesPath = App::path('Modules');
        
        // Clean up any existing test modules
        if (File::exists($this->modulesPath . '/TestModule')) {
            File::deleteDirectory($this->modulesPath . '/TestModule');
        }
    }

    protected function tearDown(): void
    {
        // Clean up test modules
        if (File::exists($this->modulesPath . '/TestModule')) {
            File::deleteDirectory($this->modulesPath . '/TestModule');
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_creates_basic_module_structure()
    {
        $this->artisan('make:module', ['name' => 'TestModule'])
            ->expectsOutput('Module TestModule created successfully!')
            ->assertExitCode(0);
        
        $modulePath = $this->modulesPath . '/TestModule';
        
        // Check basic directory structure
        $this->assertTrue(File::exists($modulePath));
        $this->assertTrue(File::exists($modulePath . '/Application'));
        $this->assertTrue(File::exists($modulePath . '/Domain'));
        $this->assertTrue(File::exists($modulePath . '/Infrastructure'));
        $this->assertTrue(File::exists($modulePath . '/Interface'));
    }

    /** @test */
    public function it_creates_module_with_all_options()
    {
        $this->artisan('make:module', [
            'name' => 'TestModule',
            '--data' => true,
            '--dto' => true,
            '--migration' => true,
            '--model' => true,
            '--repository' => true,
            '--service' => true,
            '--controller' => true,
            '--request' => true,
            '--resource' => true,
            '--route' => true,
            '--seeder' => true,
        ])->assertExitCode(0);
        
        $modulePath = $this->modulesPath . '/TestModule';
        
        // Check if specific files are created
        $this->assertTrue(File::exists($modulePath . '/Domain/Models'));
        $this->assertTrue(File::exists($modulePath . '/Infrastructure/Database/Migrations'));
        $this->assertTrue(File::exists($modulePath . '/Infrastructure/Repositories'));
        $this->assertTrue(File::exists($modulePath . '/Application/Services'));
        $this->assertTrue(File::exists($modulePath . '/Interface/Controllers'));
        $this->assertTrue(File::exists($modulePath . '/Interface/Routes'));
    }

    /** @test */
    public function it_prevents_creating_duplicate_modules()
    {
        // Create module first time
        $this->artisan('make:module', ['name' => 'TestModule'])
            ->assertExitCode(0);
        
        // Try to create same module again
        $this->artisan('make:module', ['name' => 'TestModule'])
            ->expectsOutput('Module TestModule already exists!')
            ->assertExitCode(1);
    }
}