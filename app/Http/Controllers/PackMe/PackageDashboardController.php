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

    public function index()
    {
        $packages = collect($this->helper->getPackages(base_path()));

        return view('packages.index', compact('packages'));
    }

    public function show($vendor, $name)
    {
        $path = base_path('packages/'.$vendor.'/'.$name);

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(base_path());
        $package = collect($packages)->firstWhere('full_name', $vendor.'/'.$name);

        if (!$package) {
            abort(404, 'Package not found');
        }

        $composer = [];
        if (file_exists($path.'/composer.json')) {
            $composer = json_decode(file_get_contents($path.'/composer.json'), true);
        }

        $readme = file_exists($path.'/README.md') ? file_get_contents($path.'/README.md') : null;
        $changelog = file_exists($path.'/CHANGELOG.md') ? file_get_contents($path.'/CHANGELOG.md') : null;
        $hasGit = is_dir($path.'/.git');

        return view('packages.show', compact('package', 'composer', 'readme', 'changelog', 'hasGit'));
    }

    public function publish(Request $request, $vendor, $name)
    {
        $path = base_path('packages/'.$vendor.'/'.$name);
        $type = $request->input('type', 'all');

        try {
            $this->helper->publishPackage($path, $type, base_path());

            return back()->with('success', 'Package published successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function gitInit($vendor, $name)
    {
        $path = base_path('packages/'.$vendor.'/'.$name);

        try {
            $this->helper->initGit($path);

            return back()->with('success', 'Git initialized with initial commit');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function packagist($vendor, $name)
    {
        $path = base_path('packages/'.$vendor.'/'.$name);

        if (!is_dir($path)) {
            abort(404, 'Package not found');
        }

        $packages = $this->helper->getPackages(base_path());
        $package = collect($packages)->firstWhere('full_name', $vendor.'/'.$name);

        if (!$package) {
            abort(404, 'Package not found');
        }

        $packagistUrl = 'https://packagist.org/packages/submit';

        return view('packages.packagist', compact('package', 'packagistUrl'));
    }
}
