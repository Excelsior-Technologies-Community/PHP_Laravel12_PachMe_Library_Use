<?php

namespace App\Http\Controllers\PackMe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rukhsar\PackMe\PackMeHelper;

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
     * Includes:
     * - Search
     * - Composer filter
     * - Sorting
     */
    public function index(Request $request)
    {
        $packages = collect(
            $this->helper->getPackages(base_path())
        );

        /*
         * Search by vendor, package name or full name.
         */
        $search = trim($request->input('search', ''));

        if ($search !== '') {
            $packages = $packages->filter(function ($package) use ($search) {

                return str_contains(
                    strtolower($package['vendor']),
                    strtolower($search)
                )
                ||
                str_contains(
                    strtolower($package['name']),
                    strtolower($search)
                )
                ||
                str_contains(
                    strtolower($package['full_name']),
                    strtolower($search)
                );
            });
        }

        /*
         * Composer filter.
         *
         * all     = all packages
         * yes     = packages with composer.json
         * no      = packages without composer.json
         */
        $composerFilter = $request->input('composer', 'all');

        if ($composerFilter === 'yes') {

            $packages = $packages->filter(function ($package) {
                return $package['has_composer'] === true;
            });

        } elseif ($composerFilter === 'no') {

            $packages = $packages->filter(function ($package) {
                return $package['has_composer'] === false;
            });
        }

        /*
         * Sorting.
         */
        $sort = $request->input('sort', 'name');

        switch ($sort) {

            case 'name_asc':
                $packages = $packages->sortBy('full_name');
                break;

            case 'name_desc':
                $packages = $packages->sortByDesc('full_name');
                break;

            case 'files_asc':
                $packages = $packages->sortBy('file_count');
                break;

            case 'files_desc':
                $packages = $packages->sortByDesc('file_count');
                break;

            case 'version':
                $packages = $packages->sortByDesc('version');
                break;

            default:
                $packages = $packages->sortBy('full_name');
                break;
        }

        /*
         * Reset collection indexes.
         */
        $packages = $packages->values();

        return view(
            'packages.index',
            compact(
                'packages',
                'search',
                'composerFilter',
                'sort'
            )
        );
    }

    /**
     * Show package details.
     */
    public function show($vendor, $name)
    {
        $path = base_path(
            'packages/' . $vendor . '/' . $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(base_path());

        $package = collect($packages)->firstWhere(
            'full_name',
            $vendor . '/' . $name
        );

        if (!$package) {
            abort(404, 'Package not found');
        }

        /*
         * Read composer.json.
         */
        $composer = [];

        if (file_exists($path . '/composer.json')) {

            $composer = json_decode(
                file_get_contents($path . '/composer.json'),
                true
            );

            if (!is_array($composer)) {
                $composer = [];
            }
        }

        /*
         * Read README.
         */
        $readme = file_exists($path . '/README.md')
            ? file_get_contents($path . '/README.md')
            : null;

        /*
         * Read CHANGELOG.
         */
        $changelog = file_exists($path . '/CHANGELOG.md')
            ? file_get_contents($path . '/CHANGELOG.md')
            : null;

        /*
         * Check Git.
         */
        $hasGit = is_dir($path . '/.git');

        return view(
            'packages.show',
            compact(
                'package',
                'composer',
                'readme',
                'changelog',
                'hasGit'
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
            'packages/' . $vendor . '/' . $name
        );

        $type = $request->input('type', 'all');

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
    public function gitInit($vendor, $name)
    {
        $path = base_path(
            'packages/' . $vendor . '/' . $name
        );

        try {

            $this->helper->initGit($path);

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
    public function packagist($vendor, $name)
    {
        $path = base_path(
            'packages/' . $vendor . '/' . $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(base_path());

        $package = collect($packages)->firstWhere(
            'full_name',
            $vendor . '/' . $name
        );

        if (!$package) {
            abort(404, 'Package not found');
        }

        $packagistUrl = 'https://packagist.org/packages/submit';

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
    public function download($vendor, $name)
    {
        $path = base_path(
            'packages/' . $vendor . '/' . $name
        );

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        try {

            $zipFile = $this->helper->createPackageZip(
                $path,
                $vendor,
                $name
            );

            return response()->download(
                $zipFile,
                $vendor . '-' . $name . '.zip',
                [
                    'Content-Type' => 'application/zip',
                ]
            )->deleteFileAfterSend(true);

        } catch (\Exception $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}