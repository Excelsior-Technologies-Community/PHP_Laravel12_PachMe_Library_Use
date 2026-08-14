<?php

namespace Rukhsar\PackMe;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;

class PackMeCommand extends Command
{
    protected $signature = 'pack:me {vendor} {name} {--template=basic : Package template: basic, spatie, full, api} {--force : Force overwrite existing package} {--dry-run : Preview files without creating them} {--interactive : Interactive mode} {--model= : Generate model} {--migration : Generate migration for model} {--controller= : Generate controller type: resource, api, invokable} {--routes : Generate route files} {--views : Generate blade views} {--form-request : Generate form request} {--resource : Generate API resource} {--events : Generate event and listener} {--jobs : Generate job} {--mailable : Generate mailable} {--policy : Generate policy} {--seeder : Generate seeder}';

    protected $description = 'Create a new Laravel package with advanced scaffolding';

    protected $helper;

    protected array $validTemplates = ['basic', 'spatie', 'full', 'api'];

    protected array $validControllers = ['resource', 'api', 'invokable'];

    public function __construct(PackMeHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    public function handle()
    {
        if ($this->option('interactive')) {
            return $this->handleInteractive();
        }

        $vendor = strtolower((string) $this->argument('vendor'));
        $name = strtolower((string) $this->argument('name'));
        $template = strtolower((string) $this->option('template'));
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if (!in_array($template, $this->validTemplates)) {
            $this->error("Invalid template '{$template}'. Valid options: ".implode(', ', $this->validTemplates));
            return 1;
        }

        $path = getcwd().'/packages/';
        $fullPath = $path.$vendor.'/'.$name;
        $cVendor = Str::studly($vendor);
        $cName = Str::studly($name);

        if (!$force && !$dryRun && is_dir($fullPath)) {
            $this->helper->checkExistingPackage($path, $vendor, $name);
        }

        $bar = $this->helper->progressBarSetup($this->output->createProgressBar(10));
        $bar->start();

        $this->info('Creating package '.$vendor.'\\'.$name.' with template: '.$template);

        if ($dryRun) {
            $bar->finish();
            $this->newLine();
            $this->info('[DRY-RUN] Files that would be created:');
            $this->listDryRunFiles($vendor, $name, $template);
            return 0;
        }

        $this->helper->makeDirectory($path);
        $bar->advance();
        $this->helper->makeDirectory($path.$vendor);
        $bar->advance();

        $this->copyTemplate($fullPath, $vendor, $name, $cVendor, $cName, $template);
        $bar->advance();

        $this->helper->replaceAndSave(getcwd().'/composer.json', '"psr-4": {', '"psr-4": {
            "'.$cVendor.'\\'.$cName.'\\": "packages/'.$vendor.'/'.$name.'/src",');
        $bar->advance();

        $this->helper->replaceAndSave(getcwd().'/config/app.php', 'App\Providers\RouteServiceProvider::class,', 'App\Providers\RouteServiceProvider::class,
            '.$cVendor.'\\'.$cName.'\\'.$cName.'ServiceProvider::class,');
        $bar->advance();

        $this->generateComponents($fullPath, $vendor, $name, $cVendor, $cName);
        $bar->advance();

        $bar->finish();
        $this->newLine(2);
        $this->info('Package '.$vendor.'/'.$name.' created successfully with template: '.$template);
        $this->info('Next steps:');
        $this->line('  1. cd packages/'.$vendor.'/'.$name);
        $this->line('  2. composer install');
        $this->line('  3. Add service provider if not using auto-discovery');
        $this->line('  4. Run: php artisan vendor:publish --provider="'.$cVendor.'\\'.$cName.'\\'.$cName.'ServiceProvider"');
        $this->line('  5. Git init: cd packages/'.$vendor.'/'.$name.' && git init && git add . && git commit -m "Initial commit"');
        $this->line('  6. Packagist: https://packagist.org/packages/submit');

        return 0;
    }

    protected function handleInteractive()
    {
        $vendor = $this->ask('Vendor name (e.g., acme)', 'acme');
        $name = $this->ask('Package name (e.g., blog)', 'blog');
        $template = $this->choice('Choose template', $this->validTemplates, 'basic');

        $model = $this->confirm('Generate model?', false);
        $migration = $model && $this->confirm('Generate migration?', true);
        $controllerType = $this->choice('Controller type', ['none', 'resource', 'api', 'invokable'], 'none');
        $routes = $this->confirm('Generate route files?', false);
        $views = $this->confirm('Generate blade views?', false);
        $formRequest = $this->confirm('Generate form request?', false);
        $resource = $this->confirm('Generate API resource?', false);
        $events = $this->confirm('Generate event and listener?', false);
        $jobs = $this->confirm('Generate job?', false);
        $mailable = $this->confirm('Generate mailable?', false);
        $policy = $this->confirm('Generate policy?', false);
        $seeder = $this->confirm('Generate seeder?', false);

        $this->info('Creating package '.$vendor.'\\'.$name.' interactively...');

        $cVendor = Str::studly($vendor);
        $cName = Str::studly($name);
        $path = getcwd().'/packages/';
        $fullPath = $path.$vendor.'/'.$name;

        $bar = $this->helper->progressBarSetup($this->output->createProgressBar(8));
        $bar->start();

        $this->helper->makeDirectory($path);
        $this->helper->makeDirectory($path.$vendor);
        $bar->advance();

        $this->copyTemplate($fullPath, $vendor, $name, $cVendor, $cName, $template);
        $bar->advance();

        $this->helper->replaceAndSave(getcwd().'/composer.json', '"psr-4": {', '"psr-4": {
            "'.$cVendor.'\\'.$cName.'\\": "packages/'.$vendor.'/'.$name.'/src",');
        $bar->advance();

        $this->helper->replaceAndSave(getcwd().'/config/app.php', 'App\Providers\RouteServiceProvider::class,', 'App\Providers\RouteServiceProvider::class,
            '.$cVendor.'\\'.$cName.'\\'.$cName.'ServiceProvider::class,');
        $bar->advance();

        if ($model) {
            $this->generateModel($fullPath, $vendor, $name, $cVendor, $cName, $migration);
            $bar->advance();
        }
        if ($controllerType !== 'none') {
            $this->generateController($fullPath, $vendor, $name, $cVendor, $cName, $controllerType);
            $bar->advance();
        }
        if ($routes) {
            $this->generateRoutes($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($views) {
            $this->generateViews($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($formRequest) {
            $this->generateFormRequest($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($resource) {
            $this->generateResource($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($events) {
            $this->generateEvent($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($jobs) {
            $this->generateJob($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($mailable) {
            $this->generateMailable($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($policy) {
            $this->generatePolicy($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }
        if ($seeder) {
            $this->generateSeeder($fullPath, $vendor, $name, $cVendor, $cName);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Package '.$vendor.'/'.$name.' created successfully!');

        return 0;
    }

    protected function copyTemplate(string $fullPath, string $vendor, string $name, string $cVendor, string $cName, string $template): void
    {
        $templatePath = __DIR__.'/../skeletonPackage/'.$template;
        if (!is_dir($templatePath)) {
            throw new RuntimeException("Template '{$template}' not found at {$templatePath}");
        }

        $this->line('Copying template: '.$template);
        File::copyDirectory($templatePath, $fullPath);

        $renameSearch = ['__PACKAGE_UC__', '__package_name__', '__package_uc__'];
        $renameReplace = [$cName, $name, $name];

        foreach (File::allFiles($fullPath) as $file) {
            $search = [':vendor_name', ':VendorName', ':package_name', ':PackageName'];
            $replace = [$vendor, $cVendor, $name, $cName];

            $fileContent = file_get_contents($file->getPathname());
            $newContent = str_replace($search, $replace, $fileContent);

            $targetName = str_replace($search, $replace, $file->getPathname());
            $targetName = str_replace($renameSearch, $renameReplace, $targetName);
            $targetDir = dirname($targetName);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            file_put_contents($targetName, $newContent);
            if ($file->getPathname() !== $targetName) {
                @unlink($file->getPathname());
            }
        }
    }

    protected function generateComponents(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $model = $this->option('model');
        if ($model) {
            $this->generateModel($fullPath, $vendor, $name, $cVendor, $cName, $this->option('migration'));
        }

        $controller = $this->option('controller');
        if ($controller) {
            $this->generateController($fullPath, $vendor, $name, $cVendor, $cName, $controller);
        }

        if ($this->option('routes')) {
            $this->generateRoutes($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('views')) {
            $this->generateViews($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('form-request')) {
            $this->generateFormRequest($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('resource')) {
            $this->generateResource($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('events')) {
            $this->generateEvent($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('jobs')) {
            $this->generateJob($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('mailable')) {
            $this->generateMailable($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('policy')) {
            $this->generatePolicy($fullPath, $vendor, $name, $cVendor, $cName);
        }
        if ($this->option('seeder')) {
            $this->generateSeeder($fullPath, $vendor, $name, $cVendor, $cName);
        }
    }

    protected function generateModel(string $fullPath, string $vendor, string $name, string $cVendor, string $cName, bool $migration = false): void
    {
        $modelName = Str::studly($this->option('model') ?: $name);
        $modelPath = $fullPath.'/src/Models';
        if (!is_dir($modelPath)) {
            mkdir($modelPath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$modelName} extends Model\n{\n    protected \$guarded = [];\n}\n";
        file_put_contents($modelPath.'/'.$modelName.'.php', $content);
        $this->line('  <info>[+]</info> Model: src/Models/'.$modelName.'.php');

        if ($migration) {
            $this->generateMigration($fullPath, $vendor, $name, $cVendor, $cName, $modelName);
        }
    }

    protected function generateMigration(string $fullPath, string $vendor, string $name, string $cVendor, string $cName, string $modelName): void
    {
        $date = date('Y_m_d_His');
        $tableName = strtolower($modelName).'s';
        $migrationPath = $fullPath.'/database/migrations';
        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0777, true);
        }

        $content = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        Schema::create('{$tableName}', function (Blueprint \$table) {\n            \$table->id();\n            \$table->string('name')->nullable();\n            \$table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('{$tableName}');\n    }\n};\n";
        file_put_contents($migrationPath.'/'.$date.'_create_'.$tableName.'_table.php', $content);
        $this->line('  <info>[+]</info> Migration: database/migrations/'.$date.'_create_'.$tableName.'_table.php');
    }

    protected function generateController(string $fullPath, string $vendor, string $name, string $cVendor, string $cName, string $type): void
    {
        $controllerName = $cName.'Controller';
        $controllerPath = $fullPath.'/src/Http/Controllers';
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0777, true);
        }

        $content = match ($type) {
            'resource' => "<?php\n\nnamespace {$cVendor}\\{$cName}\\Http\Controllers;\n\nuse {$cVendor}\\{$cName}\\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass {$controllerName} extends Controller\n{\n    public function index()\n    {\n        return view('{$name}::index');\n    }\n\n    public function create()\n    {\n        return view('{$name}::create');\n    }\n\n    public function store(Request \$request)\n    {\n        //\n    }\n\n    public function show(\$id)\n    {\n        //\n    }\n\n    public function edit(\$id)\n    {\n        //\n    }\n\n    public function update(Request \$request, \$id)\n    {\n        //\n    }\n\n    public function destroy(\$id)\n    {\n        //\n    }\n}\n",
            'api' => "<?php\n\nnamespace {$cVendor}\\{$cName}\\Http\Controllers\Api;\n\nuse {$cVendor}\\{$cName}\\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass {$controllerName} extends Controller\n{\n    public function index()\n    {\n        return response()->json(['message' => 'Hello from {$name} API']);\n    }\n\n    public function store(Request \$request)\n    {\n        return response()->json(['message' => 'Created'], 201);\n    }\n}\n",
            'invokable' => "<?php\n\nnamespace {$cVendor}\\{$cName}\\Http\Controllers;\n\nuse {$cVendor}\\{$cName}\\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass {$controllerName} extends Controller\n{\n    public function __invoke()\n    {\n        return view('{$name}::index');\n    }\n}\n",
            default => throw new RuntimeException('Invalid controller type'),
        };

        $file = match ($type) {
            'api' => $controllerPath.'/Api/'.$controllerName.'.php',
            default => $controllerPath.'/'.$controllerName.'.php',
        };

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $content);
        $this->line('  <info>[+]</info> Controller: '.$file);
    }

    protected function generateRoutes(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $routesPath = $fullPath.'/routes';
        if (!is_dir($routesPath)) {
            mkdir($routesPath, 0777, true);
        }

        $web = "<?php\n\nuse Illuminate\Support\Facades\Route;\nuse {$cVendor}\\{$cName}\\Http\Controllers\\{$cName}Controller;\n\nRoute::middleware('web')\n    ->prefix('{$name}')\n    ->group(function () {\n        Route::get('/', [{$cName}Controller::class, 'index']);\n    });\n";
        $api = "<?php\n\nuse Illuminate\Support\Facades\Route;\nuse {$cVendor}\\{$cName}\\Http\Controllers\Api\\{$cName}Controller;\n\nRoute::middleware('api')\n    ->prefix('api/{$name}')\n    ->group(function () {\n        Route::get('/', [{$cName}Controller::class, 'index']);\n        Route::post('/', [{$cName}Controller::class, 'store']);\n    });\n";

        file_put_contents($routesPath.'/web.php', $web);
        file_put_contents($routesPath.'/api.php', $api);
        $this->line('  <info>[+]</info> Routes: routes/web.php, routes/api.php');
    }

    protected function generateViews(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $viewsPath = $fullPath.'/resources/views';
        if (!is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }

        $index = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>{$cName}</title>\n</head>\n<body>\n    <h1>Welcome to {$cName}</h1>\n</body>\n</html>\n";
        file_put_contents($viewsPath.'/index.blade.php', $index);
        $this->line('  <info>[+]</info> Views: resources/views/index.blade.php');
    }

    protected function generateFormRequest(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $requestPath = $fullPath.'/src/Http/Requests';
        if (!is_dir($requestPath)) {
            mkdir($requestPath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Http\Requests;\n\nuse Illuminate\Foundation\Http\FormRequest;\n\nclass {$cName}Request extends FormRequest\n{\n    public function authorize()\n    {\n        return true;\n    }\n\n    public function rules()\n    {\n        return [\n            //\n        ];\n    }\n}\n";
        file_put_contents($requestPath.'/'.$cName.'Request.php', $content);
        $this->line('  <info>[+]</info> Form Request: src/Http/Requests/'.$cName.'Request.php');
    }

    protected function generateResource(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $resourcePath = $fullPath.'/src/Http/Resources';
        if (!is_dir($resourcePath)) {
            mkdir($resourcePath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Http\Resources;\n\nuse Illuminate\Http\Resources\Json\JsonResource;\n\nclass {$cName}Resource extends JsonResource\n{\n    public function toArray(\$request)\n    {\n        return [\n            'id' => \$this->id,\n        ];\n    }\n}\n";
        file_put_contents($resourcePath.'/'.$cName.'Resource.php', $content);
        $this->line('  <info>[+]</info> Resource: src/Http/Resources/'.$cName.'Resource.php');
    }

    protected function generateEvent(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $eventPath = $fullPath.'/src/Events';
        $listenerPath = $fullPath.'/src/Listeners';
        if (!is_dir($eventPath)) {
            mkdir($eventPath, 0777, true);
        }
        if (!is_dir($listenerPath)) {
            mkdir($listenerPath, 0777, true);
        }

        $event = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Events;\n\nclass {$cName}Event\n{\n    public function __construct(public mixed \$payload = [])\n    {\n    }\n}\n";
        $listener = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Listeners;\n\nuse {$cVendor}\\{$cName}\\Events\\{$cName}Event;\n\nclass {$cName}EventListener\n{\n    public function handle({$cName}Event \$event): void\n    {\n        //\n    }\n}\n";

        file_put_contents($eventPath.'/'.$cName.'Event.php', $event);
        file_put_contents($listenerPath.'/'.$cName.'EventListener.php', $listener);
        $this->line('  <info>[+]</info> Event & Listener: src/Events/, src/Listeners/');
    }

    protected function generateJob(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $jobPath = $fullPath.'/src/Jobs';
        if (!is_dir($jobPath)) {
            mkdir($jobPath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Jobs;\n\nclass {$cName}Job implements \Illuminate\Contracts\Queue\ShouldQueue\n{\n    use \Illuminate\Foundation\Bus\Dispatchable, \Illuminate\Queue\InteractsWithQueue, \Illuminate\Queue\SerializesModels;\n\n    public function __construct(public mixed \$payload = [])\n    {\n    }\n\n    public function handle(): void\n    {\n        //\n    }\n}\n";
        file_put_contents($jobPath.'/'.$cName.'Job.php', $content);
        $this->line('  <info>[+]</info> Job: src/Jobs/'.$cName.'Job.php');
    }

    protected function generateMailable(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $mailPath = $fullPath.'/src/Mail';
        if (!is_dir($mailPath)) {
            mkdir($mailPath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Mail;\n\nuse Illuminate\Mail\Mailable;\nuse Illuminate\Queue\SerializesModels;\n\nclass {$cName}Mailable extends Mailable\n{\n    use SerializesModels;\n\n    public mixed \$payload;\n\n    public function __construct(mixed \$payload = [])\n    {\n        \$this->payload = \$payload;\n    }\n\n    public function build()\n    {\n        return \$this->subject('{$cName} Mail')\n            ->view('{$name}::emails.{$name}');\n    }\n}\n";
        file_put_contents($mailPath.'/'.$cName.'Mailable.php', $content);
        $this->line('  <info>[+]</info> Mailable: src/Mail/'.$cName.'Mailable.php');
    }

    protected function generatePolicy(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $policyPath = $fullPath.'/src/Policies';
        if (!is_dir($policyPath)) {
            mkdir($policyPath, 0777, true);
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Policies;\n\nuse {$cVendor}\\{$cName}\\Models\\{$cName};\nuse Illuminate\Auth\Access\Response;\n\nclass {$cName}Policy\n{\n    public function viewAny(?User \$user): Response\n    {\n        return \$user ? Response::allow() : Response::deny();\n    }\n\n    public function view(?User \$user, {$cName} \$model): Response\n    {\n        return \$user ? Response::allow() : Response::deny();\n    }\n\n    public function create(?User \$user): Response\n    {\n        return \$user ? Response::allow() : Response::deny();\n    }\n\n    public function update(?User \$user, {$cName} \$model): Response\n    {\n        return \$user ? Response::allow() : Response::deny();\n    }\n\n    public function delete(?User \$user, {$cName} \$model): Response\n    {\n        return \$user ? Response::allow() : Response::deny();\n    }\n}\n";
        file_put_contents($policyPath.'/'.$cName.'Policy.php', $content);
        $this->line('  <info>[+]</info> Policy: src/Policies/'.$cName.'Policy.php');
    }

    protected function generateSeeder(string $fullPath, string $vendor, string $name, string $cVendor, string $cName): void
    {
        $seederPath = $fullPath.'/database/seeders';
        if (!is_dir($seederPath)) {
            mkdir($seederPath, 0777, true);
        }

        $modelOption = $this->option('model');
        $modelClass = $modelOption ? $cVendor.'\\'.$cName.'\\Models\\'.Str::studly($modelOption) : null;

        $seederClasses = '';
        $seederCalls = '';

        if ($modelClass) {
            $modelSeederName = Str::studly($modelOption ?? $name).'Seeder';
            $modelSeederContent = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Database\\Seeders;\n\nuse Illuminate\Database\Seeder;\nuse Illuminate\Support\Facades\DB;\n\nclass {$modelSeederName} extends Seeder\n{\n    public function run(): void\n    {\n        DB::table('".strtolower($modelOption ?? $name)."s')->insert([\n            [\n                'name' => 'Sample ".($modelOption ?? $name)." 1',\n                'created_at' => now(),\n                'updated_at' => now(),\n            ],\n            [\n                'name' => 'Sample ".($modelOption ?? $name)." 2',\n                'created_at' => now(),\n                'updated_at' => now(),\n            ],\n            [\n                'name' => 'Sample ".($modelOption ?? $name)." 3',\n                'created_at' => now(),\n                'updated_at' => now(),\n            ],\n        ]);\n    }\n}\n";
            file_put_contents($seederPath.'/'.$modelSeederName.'.php', $modelSeederContent);
            $this->line('  <info>[+]</info> Seeder: database/seeders/'.$modelSeederName.'.php');

            $seederClasses .= "use {$cVendor}\\{$cName}\\Database\\Seeders\\{$modelSeederName};\n";
            $seederCalls .= "        \$this->call({$modelSeederName}::class);\n";
        }

        $content = "<?php\n\nnamespace {$cVendor}\\{$cName}\\Database\\Seeders;\n\nuse Illuminate\Database\Seeder;\n\nclass DatabaseSeeder extends Seeder\n{\n    public function run(): void\n    {\n{$seederCalls}    }\n}\n";
        file_put_contents($seederPath.'/DatabaseSeeder.php', $content);
        $this->line('  <info>[+]</info> Seeder: database/seeders/DatabaseSeeder.php');
    }

    protected function listDryRunFiles(string $vendor, string $name, string $template): void
    {
        $templatePath = __DIR__.'/../skeletonPackage/'.$template;
        if (!is_dir($templatePath)) {
            $this->error("Template '{$template}' not found.");
            return;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($templatePath));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $relative = str_replace($templatePath.'/', '', $file->getPathname());
                $this->line('  <info>[+]</info> '.$relative);
            }
        }

        if ($this->option('model')) {
            $this->line('  <info>[+]</info> src/Models/'.Str::studly($this->option('model')).'.php');
        }
        if ($this->option('controller')) {
            $type = $this->option('controller');
            $this->line('  <info>[+]</info> src/Http/Controllers/'.($type === 'api' ? 'Api/' : '').$cName.'Controller.php');
        }
    }
}
