<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class MakeModifyMigrationTest extends TestCase
{
    private string $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->modulesPath = App::path('Modules');
        
        // Create test module structure
        $testModulePath = $this->modulesPath . '/TestModule/Infrastructure/Database/Migrations';
        File::makeDirectory($testModulePath, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->modulesPath . '/TestModule')) {
            File::deleteDirectory($this->modulesPath . '/TestModule');
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_creates_modify_migration_file()
    {
        $this->artisan('make:modify-migration', [
            'name' => 'modify_users_table',
            'module' => 'TestModule',
            '--table' => 'users'
        ])->assertExitCode(0);
        
        $migrationPath = $this->modulesPath . '/TestModule/Infrastructure/Database/Migrations';
        $files = File::files($migrationPath);
        
        $this->assertCount(1, $files);
        
        $migrationFile = $files[0];
        $content = File::get($migrationFile->getPathname());
        
        $this->assertStringContainsString('modify_users_table', $content);
        $this->assertStringContainsString('Schema::table', $content);
    }

    /** @test */
    public function it_requires_module_parameter()
    {
        $this->artisan('make:modify-migration', [
            'name' => 'modify_users_table'
        ])->assertExitCode(1);
    }

    /** @test */
    public function it_validates_module_exists()
    {
        $this->artisan('make:modify-migration', [
            'name' => 'modify_users_table',
            'module' => 'NonExistentModule'
        ])->expectsOutput('Module NonExistentModule does not exist!')
          ->assertExitCode(1);
    }
}