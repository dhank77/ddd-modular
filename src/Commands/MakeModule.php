<?php

namespace Vendor\DDDModularToolkit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class MakeModule extends Command
{
    protected $signature = 'make:modulefiles
        {name : Module name}
        {--A|all : Generate all components}
        {--D|data : Generate data}
        {--Y|dto : Generate DTO}
        {--M|migration : Generate migration}
        {--O|model : Generate model}
        {--R|repository : Generate repository}
        {--S|service : Generate service}
        {--C|controller : Generate controller}
        {--Q|request : Generate request}
        {--E|resource : Generate resource}
        {--T|route : Generate route}
        {--X|seeder : Generate seeder}';

    protected $description = 'Generate file-file modular DDD di folder Modules/{name}';

    protected $basePath;
    protected $moduleName;
    protected $fileName;

    public function handle()
    {
        $nameArg = $this->argument('name');
        if (strpos($nameArg, ':') !== false) {
            [$module, $controller] = explode(':', $nameArg, 2);
            $this->moduleName = Str::studly($module);
            $this->fileName = Str::studly($controller);
        } else {
            $this->moduleName = Str::studly($nameArg);
            $this->fileName = $this->moduleName;
        }
        $this->basePath = App::basePath("app/Modules/{$this->moduleName}");

        if ($this->option('all')) {
            $this->generateAll();
        } else {
            if ($this->option('data')) {
                $this->generateData();
            }
            if ($this->option('dto')) {
                $this->generateDTO();
            }
            if ($this->option('migration')) {
                $this->generateMigration();
            }
            if ($this->option('model')) {
                $this->generateModel();
            }
            if ($this->option('repository')) {
                $this->generateRepository();
            }
            if ($this->option('service')) {
                $this->generateService();
            }
            if ($this->option('controller')) {
                $this->generateController();
            }
            if ($this->option('request')) {
                $this->generateRequest();
            }
            if ($this->option('resource')) {
                $this->generateResource();
            }
            if ($this->option('route')) {
                $this->generateRoute();
            }
            if ($this->option('seeder')) {
                $this->generateSeeder();
            }

            if (
                ! $this->option('data') &&
                ! $this->option('dto') &&
                ! $this->option('migration') &&
                ! $this->option('model') &&
                ! $this->option('repository') &&
                ! $this->option('service') &&
                ! $this->option('controller') &&
                ! $this->option('resource') &&
                ! $this->option('route') &&
                ! $this->option('seeder') &&
                ! $this->option('request')
            ) {
                $this->generateAll();
            }
        }
    }

    protected function generateAll()
    {
        $this->generateData();
        $this->generateMigration();
        $this->generateModel();
        $this->generateRepository();
        $this->generateService();
        $this->generateController();
        $this->generateResource();
        $this->generateRoute();
    }

    protected function generateData()
    {
        $path = "{$this->basePath}/Application/Data";
        $this->makeDirectory($path);

        $className = "{$this->fileName}Data";
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("File data sudah ada: {$file}");

            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Application\\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Min;

class {$className} extends Data
{
    public function __construct(
        public ?int \$id,
        #[Min(3)]
        public string \$name,
    ) {}
}

PHP;

        File::put($file, $content);
        $this->info("Laravel data created: {$file}");
    }

    protected function generateDTO()
    {
        $path = "{$this->basePath}/Application/DTO";
        $this->makeDirectory($path);

        $className = "{$this->fileName}DTO";
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("DTO sudah ada: {$file}");

            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Application\\DTO;

class {$className}
{
    public function __construct(
        public readonly int \$id,
        public readonly string \$name,
    ) {}
}

PHP;

        File::put($file, $content);
        $this->info("DTO created: {$file}");
    }

    protected function generateMigration()
    {
        $path = "{$this->basePath}/Infrastructure/Database/Migrations";
        $this->makeDirectory($path);

        $timestamp = date('Y_m_d_His');
        $table = Str::snake(Str::pluralStudly($this->fileName));
        $file = "{$path}/{$timestamp}_create_{$table}_table.php";

        if (File::exists($file)) {
            $this->warn("Migration sudah ada: {$file}");

            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{$table}');
    }
};

PHP;

        File::put($file, $content);
        $this->info("Migration created: {$file}");
    }

    protected function generateModel()
    {
        $path = "{$this->basePath}/Infrastructure/Database/Models";
        $this->makeDirectory($path);

        $className = Str::singular($this->fileName);
        $file = "{$path}/{$className}.php";
        $table = Str::snake(Str::pluralStudly($this->moduleName));

        if (File::exists($file)) {
            $this->warn("Model sudah ada: {$file}");

            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Infrastructure\\Database\\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    protected \$table = '{$table}';

    protected \$fillable = ['name'];

}

PHP;

        File::put($file, $content);
        $this->info("Model created: {$file}");
    }

    protected function generateRepository()
    {
        $path = "{$this->basePath}/Infrastructure/Repositories";
        $this->makeDirectory($path);

        $interfacePath = "{$this->basePath}/Domain/Contracts";
        $this->makeDirectory($interfacePath);

        $interfaceName = "{$this->fileName}RepositoryInterface.php";
        $interfaceFile = "{$interfacePath}/{$interfaceName}";

        if (! File::exists($interfaceFile)) {
            $interfaceContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Domain\\Contracts;

interface {$this->fileName}RepositoryInterface
{

}

PHP;
            File::put($interfaceFile, $interfaceContent);
            $this->info("Repository interface created: {$interfaceFile}");
        } else {
            $this->warn("Repository interface sudah ada: {$interfaceFile}");
        }

        $className = "{$this->fileName}Repository";
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("Repository sudah ada: {$file}");

            return;
        }

        $modelName = Str::singular($this->fileName);
        $modelClass = "App\\Modules\\{$this->moduleName}\\Infrastructure\\Database\\Models\\" . $modelName;

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Infrastructure\\Repositories;

use App\\Modules\\{$this->moduleName}\\Domain\\Contracts\\{$this->fileName}RepositoryInterface;
use {$modelClass};

class {$className} implements {$this->fileName}RepositoryInterface
{
    public function __construct(
        protected {$modelName} \${$this->camelCase($this->fileName)}Model
    ) {}

}

PHP;

        File::put($file, $content);
        $this->info("Repository created: {$file}");
    }

    protected function generateService()
    {
        $path = "{$this->basePath}/Application/Services";
        $this->makeDirectory($path);

        $className = "{$this->fileName}Service";
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("service sudah ada: {$file}");

            return;
        }

        $repoInterface = "App\\Modules\\{$this->moduleName}\\Domain\\Contracts\\{$this->fileName}RepositoryInterface";

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Application\\Services;

use {$repoInterface};

class {$className}
{
    public function __construct(
        protected {$this->fileName}RepositoryInterface \${$this->camelCase($this->fileName)}Repository
    ) {}
}

PHP;

        File::put($file, $content);
        $this->info("service created: {$file}");
    }

    protected function generateController()
    {
        $path = "{$this->basePath}/Interface/Controllers";
        $this->makeDirectory($path);

        $className = $this->fileName . 'Controller';
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("Controller sudah ada: {$file}");

            return;
        }

        $service = "App\\Modules\\{$this->moduleName}\\Application\\Services\\{$this->fileName}Service";

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Interface\\Controllers;

use App\Http\Controllers\Controller;
use {$service};

class {$className} extends Controller
{

    public function __construct(
        protected {$this->fileName}Service \${$this->camelCase($this->fileName)}Service
    ) {}

}

PHP;

        File::put($file, $content);
        $this->info("Controller created: {$file}");
    }

    protected function generateRequest()
    {
        $path = "{$this->basePath}/Interface/Requests";
        $this->makeDirectory($path);

        $className = 'Submit' . $this->fileName . 'Request';
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("Form Request sudah ada: {$file}");

            return;
        }

        $dto = "App\\Modules\\{$this->moduleName}\\Application\\DTO\\{$this->moduleName}DTO";

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Interface\\Requests;

use Illuminate\Foundation\Http\FormRequest;
use {$dto};

class {$className} extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function toDTO(): {$this->moduleName}DTO
    {
        return new {$this->moduleName}DTO(
            id: \$this->input('id', 0),
            name: \$this->input('name'),
        );
    }
}

PHP;

        File::put($file, $content);
        $this->info("Form Request created: {$file}");
    }

    protected function generateResource()
    {
        $path = "{$this->basePath}/Interface/Resources";
        $this->makeDirectory($path);

        $className = $this->fileName . 'Resources';
        $file = "{$path}/{$className}.php";

        if (File::exists($file)) {
            $this->warn("File Resources sudah ada: {$file}");

            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Modules\\{$this->moduleName}\\Interface\\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class {$className} extends JsonResource
{
    public function toArray(Request \$request) : array|Arrayable|JsonSerializable
    {
        return parent::toArray(\$request);
    }
}

PHP;

        File::put($file, $content);
        $this->info("Form Request created: {$file}");
    }

    protected function generateRoute()
    {
        $path = "{$this->basePath}/Interface/Routes";
        $this->makeDirectory($path);

        $file = "{$path}/web.php";

        if (file_exists($file)) {
            $this->warn("Route file already exists: {$file}");

            return;
        }

        $prefixName = Str::lower($this->moduleName);

        $content = <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

Route::prefix('{$prefixName}')->name('{$prefixName}.')->group(function () {
    //
});

PHP;

        File::put($file, $content);
        $this->info("Route file created: {$file}");
    }

    protected function generateSeeder()
    {
        $path = "{$this->basePath}/Infrastructure/Database/Seeders";
        $namespace = "App\\Modules\\{$this->moduleName}\\Infrastructure\\Database\\Seeders";
        $className = "{$this->fileName}Seeder";
        $stub = <<<STUB
<?php

declare(strict_types=1);

namespace {$namespace};

use Illuminate\Database\Seeder;

class {$className} extends Seeder
{
    public function run(): void
    {
        //
    }
}
STUB;

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $filePath = "{$path}/{$className}.php";
        File::put($filePath, $stub);

        $databaseSeederPath = App::basePath('database/seeders/DatabaseSeeder.php');
        $content = File::get($databaseSeederPath);

        $useStatement = "use {$namespace}\\{$className};";
        if (strpos($content, $useStatement) === false) {
            $content = preg_replace(
                '/(namespace Database\\Seeders;\s+)(use .*?;\s*)*(class DatabaseSeeder)/s',
                "$1$2{$useStatement}\n$3",
                $content
            );
        }

        if (strpos($content, "{$className}::class") === false) {
            $content = preg_replace(
                '/(\$this->call\(\[\n)/',
                "$1            \\{$namespace}\\{$className}::class,\n",
                $content
            );
        }

        File::put($databaseSeederPath, $content);

        $this->info("[+] {$className} generated successfully and added to DatabaseSeeder.");
    }

    protected function makeDirectory($path)
    {
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    protected function camelCase($string)
    {
        return lcfirst(Str::studly($string));
    }
}
