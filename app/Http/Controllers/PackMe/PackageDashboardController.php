<?php

namespace App\Http\Controllers\PackMe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rukhsar\PackMe\PackMeHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackageDashboardController extends Controller
{
    protected $helper;

    public function __construct(PackMeHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Package dashboard.
     *
     * Features:
     * - Search
     * - Composer filter
     * - Git filter
     * - README filter
     * - Sorting
     * - Package size
     * - Pagination
     */
    public function index(Request $request)
    {
        $packages = collect(
            $this->helper->getPackages(base_path())
        );

        /*
        |--------------------------------------------------------------------------
        | Add calculated information
        |--------------------------------------------------------------------------
        */

        $packages = $packages->map(function ($package) {

            $path = base_path(
                'packages/' .
                    $package['vendor'] .
                    '/' .
                    $package['name']
            );

            $package['has_git'] = is_dir($path . '/.git');

            $package['has_readme'] = file_exists(
                $path . '/README.md'
            );

            $package['size_bytes'] = $this->getDirectorySize($path);

            $package['size'] = $this->formatBytes(
                $package['size_bytes']
            );

            return $package;
        });

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            $request->input('search', '')
        );

        if ($search !== '') {

            $searchLower = strtolower($search);

            $packages = $packages->filter(
                function ($package) use ($searchLower) {

                    return str_contains(
                        strtolower($package['vendor']),
                        $searchLower
                    )
                        ||
                        str_contains(
                            strtolower($package['name']),
                            $searchLower
                        )
                        ||
                        str_contains(
                            strtolower($package['full_name']),
                            $searchLower
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Composer Filter
        |--------------------------------------------------------------------------
        */

        $composerFilter = $request->input(
            'composer',
            'all'
        );

        if ($composerFilter === 'yes') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_composer'] === true
            );
        } elseif ($composerFilter === 'no') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_composer'] === false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Git Filter
        |--------------------------------------------------------------------------
        */

        $gitFilter = $request->input(
            'git',
            'all'
        );

        if ($gitFilter === 'yes') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_git'] === true
            );
        } elseif ($gitFilter === 'no') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_git'] === false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | README Filter
        |--------------------------------------------------------------------------
        */

        $readmeFilter = $request->input(
            'readme',
            'all'
        );

        if ($readmeFilter === 'yes') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_readme'] === true
            );
        } elseif ($readmeFilter === 'no') {

            $packages = $packages->filter(
                fn($package) =>
                $package['has_readme'] === false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort = $request->input(
            'sort',
            'name_asc'
        );

        switch ($sort) {

            case 'name_asc':

                $packages = $packages->sortBy(
                    'full_name'
                );

                break;

            case 'name_desc':

                $packages = $packages->sortByDesc(
                    'full_name'
                );

                break;

            case 'files_asc':

                $packages = $packages->sortBy(
                    'file_count'
                );

                break;

            case 'files_desc':

                $packages = $packages->sortByDesc(
                    'file_count'
                );

                break;

            case 'size_asc':

                $packages = $packages->sortBy(
                    'size_bytes'
                );

                break;

            case 'size_desc':

                $packages = $packages->sortByDesc(
                    'size_bytes'
                );

                break;

            case 'version':

                $packages = $packages->sortByDesc(
                    'version'
                );

                break;

            default:

                $packages = $packages->sortBy(
                    'full_name'
                );

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Collection Indexes
        |--------------------------------------------------------------------------
        */

        $packages = $packages->values();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalPackages = $packages->count();

        $totalFiles = $packages->sum(
            'file_count'
        );

        $composerPackages = $packages->filter(
            fn($package) =>
            $package['has_composer']
        )->count();

        $gitPackages = $packages->filter(
            fn($package) =>
            $package['has_git']
        )->count();

        $readmePackages = $packages->filter(
            fn($package) =>
            $package['has_readme']
        )->count();

        $totalSize = $packages->sum(
            'size_bytes'
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $request->input(
            'per_page',
            5
        );

        $allowedPerPage = [
            5,
            10,
            20,
            50
        ];

        if (!in_array(
            $perPage,
            $allowedPerPage
        )) {
            $perPage = 5;
        }

        $currentPage = max(
            1,
            (int) $request->input(
                'page',
                1
            )
        );

        $total = $packages->count();

        $paginatedPackages = $packages->forPage(
            $currentPage,
            $perPage
        );

        $lastPage = max(
            1,
            (int) ceil($total / $perPage)
        );

        /*
        |--------------------------------------------------------------------------
        | Return Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'packages.index',
            compact(
                'paginatedPackages',
                'search',
                'composerFilter',
                'gitFilter',
                'readmeFilter',
                'sort',
                'perPage',
                'currentPage',
                'lastPage',
                'total',
                'totalPackages',
                'totalFiles',
                'composerPackages',
                'gitPackages',
                'readmePackages',
                'totalSize'
            )
        );
    }

    /**
     * Show package details.
     */
    public function show($vendor, $name)
    {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(
            base_path()
        );

        $package = collect($packages)->firstWhere(
            'full_name',
            $vendor . '/' . $name
        );

        if (!$package) {
            abort(404, 'Package not found');
        }

        /*
        |--------------------------------------------------------------------------
        | Composer
        |--------------------------------------------------------------------------
        */

        $composer = [];

        if (file_exists(
            $path . '/composer.json'
        )) {

            $composer = json_decode(
                file_get_contents(
                    $path . '/composer.json'
                ),
                true
            );

            if (!is_array($composer)) {
                $composer = [];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | README
        |--------------------------------------------------------------------------
        */

        $readme = file_exists(
            $path . '/README.md'
        )
            ? file_get_contents(
                $path . '/README.md'
            )
            : null;

        /*
        |--------------------------------------------------------------------------
        | CHANGELOG
        |--------------------------------------------------------------------------
        */

        $changelog = file_exists(
            $path . '/CHANGELOG.md'
        )
            ? file_get_contents(
                $path . '/CHANGELOG.md'
            )
            : null;

        /*
        |--------------------------------------------------------------------------
        | Git
        |--------------------------------------------------------------------------
        */

        $hasGit = is_dir(
            $path . '/.git'
        );

        /*
        |--------------------------------------------------------------------------
        | Package Size
        |--------------------------------------------------------------------------
        */

        $sizeBytes = $this->getDirectorySize(
            $path
        );

        $size = $this->formatBytes(
            $sizeBytes
        );

        /*
        |--------------------------------------------------------------------------
        | Last Modified
        |--------------------------------------------------------------------------
        */

        $lastModified = file_exists($path)
            ? date(
                'Y-m-d H:i:s',
                filemtime($path)
            )
            : null;

        return view(
            'packages.show',
            compact(
                'package',
                'composer',
                'readme',
                'changelog',
                'hasGit',
                'size',
                'sizeBytes',
                'lastModified'
            )
        );
    }

    /**
     * Publish package assets.
     */
    public function publish(
        Request $request,
        $vendor,
        $name
    ) {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $type = $request->input(
            'type',
            'all'
        );

        try {

            $this->helper->publishPackage(
                $path,
                $type,
                base_path()
            );

            return back()->with(
                'success',
                'Package published successfully!'
            );
        } catch (\Exception $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * Initialize Git.
     */
    public function gitInit(
        $vendor,
        $name
    ) {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        try {

            $this->helper->initGit(
                $path
            );

            return back()->with(
                'success',
                'Git initialized with initial commit'
            );
        } catch (\Exception $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * Packagist guide.
     */
    public function packagist(
        $vendor,
        $name
    ) {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(
            base_path()
        );

        $package = collect($packages)->firstWhere(
            'full_name',
            $vendor . '/' . $name
        );

        if (!$package) {
            abort(404, 'Package not found');
        }

        $packagistUrl =
            'https://packagist.org/packages/submit';

        return view(
            'packages.packagist',
            compact(
                'package',
                'packagistUrl'
            )
        );
    }

    /**
     * Download package as ZIP.
     */
    public function download(
        $vendor,
        $name
    ) {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        try {

            $zipFile =
                $this->helper->createPackageZip(
                    $path,
                    $vendor,
                    $name
                );

            return response()->download(
                $zipFile,
                $vendor . '-' . $name . '.zip',
                [
                    'Content-Type' =>
                    'application/zip',
                ]
            )->deleteFileAfterSend(true);
        } catch (\Exception $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * Delete package.
     */
    public function destroy(
        Request $request,
        $vendor,
        $name
    ) {
        $path = base_path(
            'packages/' .
                $vendor .
                '/' .
                $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        $packagesRoot = realpath(
            base_path('packages')
        );

        $packageRealPath = realpath($path);

        if (
            !$packagesRoot ||
            !$packageRealPath ||
            !str_starts_with(
                $packageRealPath,
                $packagesRoot . DIRECTORY_SEPARATOR
            )
        ) {
            abort(
                403,
                'Invalid package path'
            );
        }

        try {

            $this->deleteDirectory(
                $packageRealPath
            );

            return redirect()
                ->route('packages.index')
                ->with(
                    'success',
                    'Package ' .
                        $vendor .
                        '/' .
                        $name .
                        ' deleted successfully.'
                );
        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Unable to delete package: ' .
                    $e->getMessage()
            );
        }
    }

    /**
     * Export packages as CSV.
     */
    public function export(
        Request $request
    ): StreamedResponse {

        /*
        |--------------------------------------------------------------------------
        | Build same filtered package collection
        |--------------------------------------------------------------------------
        */

        $packages = collect(
            $this->helper->getPackages(
                base_path()
            )
        );

        $packages = $packages->map(
            function ($package) {

                $path = base_path(
                    'packages/' .
                        $package['vendor'] .
                        '/' .
                        $package['name']
                );

                $package['has_git'] =
                    is_dir(
                        $path . '/.git'
                    );

                $package['has_readme'] =
                    file_exists(
                        $path . '/README.md'
                    );

                $package['size_bytes'] =
                    $this->getDirectorySize(
                        $path
                    );

                $package['size'] =
                    $this->formatBytes(
                        $package['size_bytes']
                    );

                return $package;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {

            $searchLower =
                strtolower($search);

            $packages =
                $packages->filter(
                    function ($package)
                    use ($searchLower) {

                        return
                            str_contains(
                                strtolower(
                                    $package['vendor']
                                ),
                                $searchLower
                            )
                            ||
                            str_contains(
                                strtolower(
                                    $package['name']
                                ),
                                $searchLower
                            )
                            ||
                            str_contains(
                                strtolower(
                                    $package['full_name']
                                ),
                                $searchLower
                            );
                    }
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Composer
        |--------------------------------------------------------------------------
        */

        $composer =
            $request->input(
                'composer',
                'all'
            );

        if ($composer === 'yes') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    $p['has_composer']
                );
        } elseif ($composer === 'no') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    !$p['has_composer']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Git
        |--------------------------------------------------------------------------
        */

        $git =
            $request->input(
                'git',
                'all'
            );

        if ($git === 'yes') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    $p['has_git']
                );
        } elseif ($git === 'no') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    !$p['has_git']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | README
        |--------------------------------------------------------------------------
        */

        $readme =
            $request->input(
                'readme',
                'all'
            );

        if ($readme === 'yes') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    $p['has_readme']
                );
        } elseif ($readme === 'no') {

            $packages =
                $packages->filter(
                    fn($p) =>
                    !$p['has_readme']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | CSV Response
        |--------------------------------------------------------------------------
        */

        $filename =
            'packages-' .
            date('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($packages) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv(
                    $handle,
                    [
                        'Vendor',
                        'Package',
                        'Full Name',
                        'Version',
                        'Files',
                        'Size',
                        'Composer',
                        'Git',
                        'README',
                    ]
                );

                foreach ($packages as $package) {

                    fputcsv(
                        $handle,
                        [
                            $package['vendor'],
                            $package['name'],
                            $package['full_name'],
                            $package['version'],
                            $package['file_count'],
                            $package['size'],
                            $package['has_composer']
                                ? 'Yes'
                                : 'No',
                            $package['has_git']
                                ? 'Yes'
                                : 'No',
                            $package['has_readme']
                                ? 'Yes'
                                : 'No',
                        ]
                    );
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                'text/csv',
            ]
        );
    }

    /**
     * Calculate directory size.
     */
    private function getDirectorySize(
        string $directory
    ): int {

        if (!is_dir($directory)) {
            return 0;
        }

        $size = 0;

        try {

            $iterator =
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $directory,
                        \FilesystemIterator::SKIP_DOTS
                    )
                );

            foreach ($iterator as $file) {

                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {

            return 0;
        }

        return $size;
    }

    /**
     * Format bytes.
     */
    private function formatBytes(
        int $bytes
    ): string {

        if ($bytes <= 0) {
            return '0 B';
        }

        $units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];

        $power = floor(
            log($bytes, 1024)
        );

        $power = min(
            $power,
            count($units) - 1
        );

        return number_format(
            $bytes / pow(1024, $power),
            2
        ) . ' ' . $units[$power];
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(
        string $directory
    ): void {

        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items as $item) {

            if (
                $item === '.' ||
                $item === '..'
            ) {
                continue;
            }

            $path =
                $directory .
                DIRECTORY_SEPARATOR .
                $item;

            if (is_dir($path)) {

                $this->deleteDirectory(
                    $path
                );
            } else {

                if (!unlink($path)) {
                    throw new \RuntimeException(
                        'Unable to delete file: ' .
                            $path
                    );
                }
            }
        }

        if (!rmdir($directory)) {

            throw new \RuntimeException(
                'Unable to delete package directory.'
            );
        }
    }
}
