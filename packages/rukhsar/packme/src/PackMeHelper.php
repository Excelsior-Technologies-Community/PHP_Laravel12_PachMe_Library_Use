<?php

namespace Rukhsar\PackMe;

use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

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
            throw new RuntimeException('Package already exists, choose a different name or use --force');
        }
    }

    public function makeDirectory($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
    }

    public function replaceAndSave($oldFile, $search, $replace, $newFile = null, $deleteOldFiles = false)
    {
        $newFile = ($newFile === null) ? $oldFile : $newFile;

        $file = $this->files->get($oldFile);

        $replacing = str_replace($search, $replace, $file);

        $this->files->put($newFile, $replacing);

        if ($deleteOldFiles) {
            $this->files->delete($oldFile);
        }
    }

    public function publishPackage($path, $type = 'all', $basePath = null)
    {
        $basePath = $basePath ?: getcwd();
        $providerPath = $path.'/src/'.basename($path).'ServiceProvider.php';

        if (!file_exists($providerPath)) {
            throw new RuntimeException('Service provider not found in package');
        }

        $this->line('Publishing package assets...');

        if ($type === 'all' || $type === 'config') {
            $this->publishConfig($path, $basePath);
        }
        if ($type === 'all' || $type === 'views') {
            $this->publishViews($path, $basePath);
        }
        if ($type === 'all' || $type === 'migrations') {
            $this->publishMigrations($path, $basePath);
        }

        $this->info('Package published successfully!');
    }

    protected function publishConfig($path, $basePath)
    {
        $configSrc = $path.'/config';
        $configDest = $basePath.'/config';

        if (is_dir($configSrc)) {
            foreach (glob($configSrc.'/*.php') as $file) {
                $fileName = basename($file);
                copy($file, $configDest.'/'.$fileName);
                $this->line("  <info>[+]</info> Config: {$fileName}");
            }
        }
    }

    protected function publishViews($path, $basePath)
    {
        $viewsSrc = $path.'/resources/views';
        $viewsDest = $basePath.'/resources/views/vendor/'.basename($path);

        if (is_dir($viewsSrc)) {
            if (!is_dir($viewsDest)) {
                mkdir($viewsDest, 0777, true);
            }
            $this->copyDirectory($viewsSrc, $viewsDest);
            $this->line("  <info>[+]</info> Views published to resources/views/vendor/".basename($path));
        }
    }

    protected function publishMigrations($path, $basePath)
    {
        $migrationsSrc = $path.'/database/migrations';
        $migrationsDest = $basePath.'/database/migrations';

        if (is_dir($migrationsSrc)) {
            foreach (glob($migrationsSrc.'/*.php') as $file) {
                $fileName = basename($file);
                copy($file, $migrationsDest.'/'.$fileName);
                $this->line("  <info>[+]</info> Migration: {$fileName}");
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
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    public function initGit($path)
    {
        if (!is_dir($path.'/.git')) {
            shell_exec('cd '.escapeshellarg($path).' && git init');
            shell_exec('cd '.escapeshellarg($path).' && git add .');
            shell_exec('cd '.escapeshellarg($path).' && git commit -m "Initial commit"');
            $this->info('Git initialized with initial commit');
        } else {
            $this->warn('Git repository already exists');
        }
    }

    public function getPackages(string $basePath = null)
    {
        $packagesPath = ($basePath ?: getcwd()).'/packages';
        $packages = [];

        if (!is_dir($packagesPath)) {
            return $packages;
        }

        foreach (glob($packagesPath.'/*/*', GLOB_ONLYDIR) as $packageDir) {
            $vendor = basename(dirname($packageDir));
            $name = basename($packageDir);

            $composerPath = $packageDir.'/composer.json';
            $hasComposer = file_exists($composerPath);
            $version = '0.0.1';

            if ($hasComposer) {
                $composer = json_decode(file_get_contents($composerPath), true);
                $version = $composer['version'] ?? $version;
            }

            $files = [];
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packageDir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = str_replace($packageDir.'/', '', $file->getPathname());
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
}
