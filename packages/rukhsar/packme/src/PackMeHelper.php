<?php

namespace Rukhsar\PackMe;

use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use ZipArchive;

class PackMeHelper
{
    protected $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function progressBarSetup(ProgressBar $bar)
    {
        $bar->setBarCharacter('<comment>=</comment>');
        $bar->setEmptyBarCharacter('-');
        $bar->setProgressCharacter('>');
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% ');

        return $bar;
    }

    public function checkExistingPackage($path, $vendor, $name)
    {
        if (is_dir($path.$vendor.'/'.$name)) {
            throw new RuntimeException(
                'Package already exists, choose a different name or use --force'
            );
        }
    }

    public function makeDirectory($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }

        return true;
    }

    public function replaceAndSave(
        $oldFile,
        $search,
        $replace,
        $newFile = null,
        $deleteOldFiles = false
    ) {
        $newFile = ($newFile === null) ? $oldFile : $newFile;

        $file = $this->files->get($oldFile);

        $replacing = str_replace($search, $replace, $file);

        $this->files->put($newFile, $replacing);

        if ($deleteOldFiles) {
            $this->files->delete($oldFile);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Publish Package
    |--------------------------------------------------------------------------
    */

    public function publishPackage($path, $type = 'all', $basePath = null)
    {
        $basePath = $basePath ?: getcwd();

        $providerPath = $path.'/src/'.basename($path).'ServiceProvider.php';

        if (!file_exists($providerPath)) {
            throw new RuntimeException(
                'Service provider not found in package'
            );
        }

        if ($type === 'all' || $type === 'config') {
            $this->publishConfig($path, $basePath);
        }

        if ($type === 'all' || $type === 'views') {
            $this->publishViews($path, $basePath);
        }

        if ($type === 'all' || $type === 'migrations') {
            $this->publishMigrations($path, $basePath);
        }
    }

    protected function publishConfig($path, $basePath)
    {
        $configSrc = $path.'/config';
        $configDest = $basePath.'/config';

        if (is_dir($configSrc)) {

            if (!is_dir($configDest)) {
                mkdir($configDest, 0777, true);
            }

            foreach (glob($configSrc.'/*.php') as $file) {

                $fileName = basename($file);

                copy(
                    $file,
                    $configDest.'/'.$fileName
                );
            }
        }
    }

    protected function publishViews($path, $basePath)
    {
        $viewsSrc = $path.'/resources/views';

        $viewsDest =
            $basePath.'/resources/views/vendor/'.basename($path);

        if (is_dir($viewsSrc)) {

            if (!is_dir($viewsDest)) {
                mkdir($viewsDest, 0777, true);
            }

            $this->copyDirectory(
                $viewsSrc,
                $viewsDest
            );
        }
    }

    protected function publishMigrations($path, $basePath)
    {
        $migrationsSrc = $path.'/database/migrations';

        $migrationsDest =
            $basePath.'/database/migrations';

        if (is_dir($migrationsSrc)) {

            if (!is_dir($migrationsDest)) {
                mkdir($migrationsDest, 0777, true);
            }

            foreach (glob($migrationsSrc.'/*.php') as $file) {

                $fileName = basename($file);

                copy(
                    $file,
                    $migrationsDest.'/'.$fileName
                );
            }
        }
    }

    protected function copyDirectory($src, $dst)
    {
        $dir = opendir($src);

        if (!is_dir($dst)) {
            mkdir($dst, 0777, true);
        }

        while (($file = readdir($dir)) !== false) {

            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $src.'/'.$file;
            $dstPath = $dst.'/'.$file;

            if (is_dir($srcPath)) {

                $this->copyDirectory(
                    $srcPath,
                    $dstPath
                );

            } else {

                copy(
                    $srcPath,
                    $dstPath
                );
            }
        }

        closedir($dir);
    }

    /*
    |--------------------------------------------------------------------------
    | Git
    |--------------------------------------------------------------------------
    */

    public function initGit($path)
    {
        if (!is_dir($path.'/.git')) {

            shell_exec(
                'cd '.escapeshellarg($path).' && git init'
            );

            shell_exec(
                'cd '.escapeshellarg($path).' && git add .'
            );

            shell_exec(
                'cd '.escapeshellarg($path).
                ' && git commit -m "Initial commit"'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Packages
    |--------------------------------------------------------------------------
    */

    public function getPackages(string $basePath = null)
    {
        $packagesPath =
            ($basePath ?: getcwd()).'/packages';

        $packages = [];

        if (!is_dir($packagesPath)) {
            return $packages;
        }

        foreach (
            glob(
                $packagesPath.'/*/*',
                GLOB_ONLYDIR
            ) as $packageDir
        ) {

            $vendor =
                basename(dirname($packageDir));

            $name =
                basename($packageDir);

            $composerPath =
                $packageDir.'/composer.json';

            $hasComposer =
                file_exists($composerPath);

            $version = '0.0.1';

            if ($hasComposer) {

                $composer = json_decode(
                    file_get_contents($composerPath),
                    true
                );

                $version =
                    $composer['version'] ?? $version;
            }

            $files = [];

            $iterator =
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $packageDir,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    )
                );

            foreach ($iterator as $file) {

                if ($file->isFile()) {

                    $relativePath = str_replace(
                        $packageDir.DIRECTORY_SEPARATOR,
                        '',
                        $file->getPathname()
                    );

                    $files[] =
                        str_replace(
                            DIRECTORY_SEPARATOR,
                            '/',
                            $relativePath
                        );
                }
            }

            $packages[] = [
                'vendor' => $vendor,
                'name' => $name,
                'full_name' => $vendor.'/'.$name,
                'path' => $packageDir,
                'version' => $version,
                'has_composer' => $hasComposer,
                'file_count' => count($files),
                'files' => $files,
            ];
        }

        return $packages;
    }

    /*
    |--------------------------------------------------------------------------
    | NEW FEATURE: Create Package ZIP
    |--------------------------------------------------------------------------
    */

    public function createPackageZip(
        $packagePath,
        $vendor,
        $name
    ) {
        /*
         * Check package exists.
         */
        if (!is_dir($packagePath)) {
            throw new RuntimeException(
                'Package not found.'
            );
        }

        /*
         * Check PHP ZIP extension.
         */
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException(
                'PHP ZIP extension is not enabled. ' .
                'Please enable extension=zip in php.ini.'
            );
        }

        /*
         * Create temporary ZIP directory.
         */
        $zipDirectory =
            storage_path('app/packages');

        if (!is_dir($zipDirectory)) {

            mkdir(
                $zipDirectory,
                0777,
                true
            );
        }

        /*
         * ZIP file name.
         */
        $zipFile =
            $zipDirectory .
            DIRECTORY_SEPARATOR .
            $vendor .
            '-' .
            $name .
            '.zip';

        /*
         * Delete old ZIP if exists.
         */
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        /*
         * Create ZIP.
         */
        $zip = new ZipArchive();

        $result = $zip->open(
            $zipFile,
            ZipArchive::CREATE |
            ZipArchive::OVERWRITE
        );

        if ($result !== true) {
            throw new RuntimeException(
                'Unable to create ZIP file.'
            );
        }

        /*
         * Read all package files.
         */
        $iterator =
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $packagePath,
                    \RecursiveDirectoryIterator::SKIP_DOTS
                )
            );

        foreach ($iterator as $file) {

            if (!$file->isFile()) {
                continue;
            }

            /*
             * Absolute file path.
             */
            $filePath =
                $file->getPathname();

            /*
             * Relative path.
             */
            $relativePath =
                str_replace(
                    $packagePath .
                    DIRECTORY_SEPARATOR,
                    '',
                    $filePath
                );

            /*
             * Convert Windows \ to /.
             */
            $relativePath =
                str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    $relativePath
                );

            /*
             * Put package inside a folder.
             *
             * Example:
             *
             * rukhsar-demo/
             * ├── composer.json
             * ├── README.md
             * └── src/
             */
            $zipPath =
                $vendor .
                '-' .
                $name .
                '/' .
                $relativePath;

            $zip->addFile(
                $filePath,
                $zipPath
            );
        }

        /*
         * Close ZIP.
         */
        $zip->close();

        /*
         * Verify ZIP was created.
         */
        if (!file_exists($zipFile)) {
            throw new RuntimeException(
                'ZIP file could not be created.'
            );
        }

        return $zipFile;
    }
}
