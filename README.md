# PHP_Laravel12_PachMe_Library_Use


# Step 1 : Install Laravel 12 
```php
composer create-project laravel/laravel PHP_Laravel12_PachMe_Library_Use
```
# Step 2 : Open Project Folder PHP_Laravel12_PachMe_Library_Use
# Step 3  : Create Package file folder
```php
 packages/rukhsar/packme/src
```
# Create three file for src folder
# packages/rukhsar/packme/src/ PackMeCommand.php
```php
<?php

namespace Rukhsar\PackMe;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PackMeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pack:me {vendor} {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $helper;

    public function __construct(PackMeHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Progressbar Setup
        $bar= $this->helper->progressBarSetup($this->output->createProgressBar(7));

        // Start Progressbar
        $bar->start();

        // Command's common variables setup
        $vendor = strtolower($this->argument('vendor'));
        $name = strtolower($this->argument('name'));
        $path = getcwd().'/packages/';
        $fullPath = $path.$vendor.'/'.$name;

        $cVendor = studly_case($vendor);
        $cName = studly_case($name);

        $requirement = '"psr-4": {
            "' . $cVendor . '\\\\' . $cName . '\\\\": "packages/' . $vendor . '/' . $name . '/src",';

        $appConfigLine = 'App\Providers\RouteServiceProvider::class,
            ' . $cVendor . '\\' . $cName . '\\' . 'ServiceProvider::class,';

        // Start creating the package
        $this->info('Creating package '.$vendor.'\\'.$name.'...');

        // Check if the package already exist with this name and vendor
        if (!$this->option('force')) {
            $this->helper->checkExistingPackage($path, $vendor, $name);
        }

        // Move the progressbar to show progress
        $bar->advance();

        // Creating package directory
        $this->info('Creating package directory...');
        $this->helper->makeDirectory($path);

        // Move the progressbar to show progress
        $bar->advance();

        // Creating vendor directory
        $this->info('Creating vendor...');
        $this->helper->makeDirectory($path.$vendor);

        // Move the progressbar to show progress
        $bar->advance();

        // Copying the skeleton package
        $this->info('Creating skeleton package');
        File::copyDirectory(__DIR__.'/../skeletonPackage', $fullPath);

        foreach (File::allFiles($fullPath) as $file)
        {
            $search = [':vendor_name',':VendorName',':package_name',':PackageName'];

            $replace = [$vendor, $cVendor, $name, $cName];

            $newFile = substr($file, 0, -5);

            $this->helper->replaceAndSave($file, $search, $replace, $newFile, $deleteOldFiles = true);
        }

        // Move the progressbar to show progress
        $bar->advance();

        // Adding package to Composer.json
        $this->info('Adding package to composer and app...');

        $this->helper->replaceAndSave(getcwd() . '/composer.json', '"psr-4": {', $requirement);

        //And add it to the providers array in config/app.php
        $this->helper->replaceAndSave(getcwd() . '/config/app.php', 'App\Providers\RouteServiceProvider::class,',$appConfigLine);

        // Move the progressbar to show progress
        $bar->advance();

        // Finished creating the package, end of the progress bar
        $bar->finish();

        $this->info('Package created successfully!');

        $this->output->newLine(2);

        $bar = null;

    }
}
```

# packages/rukhsar/packme/src/ PackMeHelper.php
```php
<?php

namespace Rukhsar\PackMe;

use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

class PackMeHelper
{
    /**
     * The filesystem handler
     * @var [type]
     */
    protected $files;

    /**
     * Create a new instance of File System
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Setting custom formatting for the progress bar
     * @param  object $bar Symfony ProgressBar instance
     * @return object $bar Symfony ProgressBar instance
     */
    public function progressBarSetup(ProgressBar $bar)
    {
        $bar->setBarCharacter('<comment>=</comment>');

        $bar->setEmptyBarCharacter('-');

        $bar->setProgressCharacter('>');

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% ');

        return $bar;
    }

    /**
     * Check if the package already exists
     * @param  string $path   Path to the package directory
     * @param  string $vendor The vendor
     * @param  string $name   Name of the package
     * @return void           Throws error if package exists, aborts process
     */
    public function checkExistingPackage($path, $vendor, $name)
    {
        if(is_dir($path.$vendor.'/'.$name))
        {
            throw new RuntimeException('Package already exist, choose a different name');
        }
    }

    /**
     * Create a directory if it doesn't exist
     * @param  string $path Path of the directory to make
     * @return void
     */
    public function makeDirectory($path)
    {
        if(!is_dir($path))
        {
            return mkdir($path);
        }
    }

    /**
     * Open haystack, find and replace needles, save haystack
     * @param  string $oldFile The haystack
     * @param  mixed  $search  String or array to look for (the needles)
     * @param  mixed  $replace What to replace the needles for?
     * @param  string $newFile Where to save, defaults to $oldFile
     * @param  boolean $deleteOldFile Whether to delete $oldFile or not
     * @return void
     */
    public function replaceAndSave($oldFile, $search, $replace, $newFile = null, $deleteOldFiles = false)
    {
        $newFile = ($newFile === null) ? $oldFile : $newFile ;

        $file = $this->files->get($oldFile);

        $replacing = str_replace($search, $replace, $file);

        $this->files->put($newFile, $replacing);

        if($deleteOldFiles)
            $this->files->delete($oldFile);
    }
}
```
# packages/rukhsar/packme/src/ PackMeServiceProvider.php
```php
<?php

namespace Rukhsar\PackMe;

use Illuminate\Support\ServiceProvider;

class PackMeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if the loading of the provider is deferred
     * @var boolean
     */
    protected $defer = false;

    /**
     * The console commands to register
     * @var array
     */
    protected $commands = ['Rukhsar\PackMe\PackMeCommand'];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * Get the service provided by the provider
     * @return [type] [description]
     */
    public function provides()
    {
        return ['packmecommand'];
    }}
```
# Step 4  : Open composer.json file and add “autoload method ”
```php
 "Rukhsar\\PackMe\\": "packages/rukhsar/packme/src/"
```

# Step 5 : Run Command For Terminal
```php
composer dump-autoload
```
# Step 6  :  After Successful then Adding  Web.php route
```php
use Rukhsar\PackMe\PackMeHelper;

Route::get('/packme-test', function () {
    return "PackMe Loaded Successfully!";
});
```
# Now Run Server and paste this url from browser
```php
php artisan serve
```
```php
http://127.0.0.1:8000/packme-test
 ```
<img width="1434" height="459" alt="image" src="https://github.com/user-attachments/assets/e3704fed-1fbb-43a7-ad06-d75f4393eec1" />



