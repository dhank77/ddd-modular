<?php

declare(strict_types=1);

namespace Hitech\DDDModularToolkit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class MakeModifyMigration extends Command
{
    protected $signature = 'modify:migration
        {module}
        {table}
        {--add-column=}
        {--ac=}
        {--rename-column=}
        {--rc=}
        {--drop-column=}
        {--dc=}
        {--modify-column=}
        {--mc=}';

    protected $description = 'Create a new migration file for modifying existing table in specific module';

    public function handle()
    {
        $module = $this->argument('module');
        $table = $this->argument('table');
        $addColumn = $this->option('add-column') ?: $this->option('ac');
        $renameColumn = $this->option('rename-column') ?: $this->option('rc');
        $dropColumn = $this->option('drop-column') ?: $this->option('dc');
        $modifyColumn = $this->option('modify-column') ?: $this->option('mc');

        if (! $addColumn && ! $renameColumn && ! $dropColumn && ! $modifyColumn) {
            $this->error('Minimal satu opsi harus dipilih: --ac, --rc, --dc, atau --mc');

            return 1;
        }

        $this->generateModifyMigration($module, $table, $addColumn, $renameColumn, $dropColumn, $modifyColumn);

        return 0;
    }

    protected function generateModifyMigration($module, $table, $addColumn, $renameColumn, $dropColumn, $modifyColumn)
    {
        $basePath = App::path("Modules/{$module}");
        $path = "{$basePath}/Infrastructure/Database/Migrations";

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $className = $this->generateClassName($table, $addColumn, $renameColumn, $dropColumn, $modifyColumn);
        $file = "{$path}/{$timestamp}_{$className}.php";

        if (File::exists($file)) {
            $this->warn("Migration sudah ada: {$file}");

            return;
        }

        $content = $this->generateMigrationContent($table, $className, $addColumn, $renameColumn, $dropColumn, $modifyColumn);

        File::put($file, $content);
        $this->info("Migration created: {$file}");
    }

    protected function generateClassName($table, $addColumn, $renameColumn, $dropColumn, $modifyColumn)
    {
        $actions = [];

        if ($addColumn) {
            $actions[] = 'add_' . Str::snake($addColumn) . '_to';
        }
        if ($renameColumn) {
            $actions[] = 'rename_column_in';
        }
        if ($dropColumn) {
            $actions[] = 'drop_' . Str::snake($dropColumn) . '_from';
        }
        if ($modifyColumn) {
            $actions[] = 'modify_' . Str::snake($modifyColumn) . '_in';
        }

        return implode('_and_', $actions) . '_' . $table . '_table';
    }

    protected function generateMigrationContent($table, $className, $addColumn, $renameColumn, $dropColumn, $modifyColumn)
    {
        $upMethods = [];
        $downMethods = [];

        // Add Column
        if ($addColumn) {
            $columnName = Str::snake($addColumn);
            $upMethods[] = "            \$table->string('{$columnName}')->nullable();";
            $downMethods[] = "            \$table->dropColumn('{$columnName}');";
        }

        // Rename Column
        if ($renameColumn) {
            $columns = explode(':', $renameColumn);
            if (count($columns) === 2) {
                $oldName = Str::snake($columns[0]);
                $newName = Str::snake($columns[1]);
                $upMethods[] = "            \$table->renameColumn('{$oldName}', '{$newName}');";
                $downMethods[] = "            \$table->renameColumn('{$newName}', '{$oldName}');";
            }
        }

        // Drop Column
        if ($dropColumn) {
            $columnName = Str::snake($dropColumn);
            $upMethods[] = "            \$table->dropColumn('{$columnName}');";
            $downMethods[] = "            \$table->string('{$columnName}')->nullable();";
        }

        // Modify Column
        if ($modifyColumn) {
            $columnName = Str::snake($modifyColumn);
            $upMethods[] = "            \$table->text('{$columnName}')->change();";
            $downMethods[] = "            \$table->string('{$columnName}')->change();";
        }

        $upMethodsStr = implode("\n", $upMethods);
        $downMethodsStr = implode("\n", $downMethods);

        return <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('{$table}', function (Blueprint \$table) {
{$upMethodsStr}
        });
    }

    public function down()
    {
        Schema::table('{$table}', function (Blueprint \$table) {
{$downMethodsStr}
        });
    }
};

PHP;
    }
}
