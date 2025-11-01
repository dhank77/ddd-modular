<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class PathHelperTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testDir = sys_get_temp_dir() . '/ddd_test_' . uniqid();
        File::makeDirectory($this->testDir, 0755, true);
        
        // Create test file structure
        File::makeDirectory($this->testDir . '/Module1/Routes', 0755, true);
        File::makeDirectory($this->testDir . '/Module2/Routes', 0755, true);
        File::put($this->testDir . '/Module1/Routes/web.php', '<?php // test route');
        File::put($this->testDir . '/Module2/Routes/web.php', '<?php // test route 2');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }
        
        parent::tearDown();
    }

    /** @test */
    public function glob_recursive_finds_matching_files()
    {
        $foundFiles = [];
        
        globRecursive(
            $this->testDir . '/*/Routes/web.php',
            function (string $file) use (&$foundFiles) {
                $foundFiles[] = $file;
            }
        );
        
        $this->assertCount(2, $foundFiles);
        $this->assertStringContainsString('Module1/Routes/web.php', $foundFiles[0]);
        $this->assertStringContainsString('Module2/Routes/web.php', $foundFiles[1]);
    }

    /** @test */
    public function glob_recursive_handles_no_matches()
    {
        $foundFiles = [];
        
        globRecursive(
            $this->testDir . '/*/NonExistent/*.php',
            function (string $file) use (&$foundFiles) {
                $foundFiles[] = $file;
            }
        );
        
        $this->assertCount(0, $foundFiles);
    }
}