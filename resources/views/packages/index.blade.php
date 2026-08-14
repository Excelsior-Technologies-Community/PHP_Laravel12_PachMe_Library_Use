<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PackMe Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 2rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #6366f1; }
        .stat-label { color: #666; font-size: 0.9rem; margin-top: 0.5rem; }
        .packages { display: grid; gap: 1rem; }
        .package-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .package-info h3 { color: #333; margin-bottom: 0.5rem; }
        .package-info p { color: #666; font-size: 0.9rem; }
        .package-actions { display: flex; gap: 0.5rem; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; display: inline-block; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-secondary { background: #e5e7eb; color: #333; }
        .btn-success { background: #10b981; color: white; }
        .btn:hover { opacity: 0.9; }
        .empty { text-align: center; padding: 3rem; color: #666; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 PackMe Dashboard</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @php($packages = is_array($packages) ? collect($packages) : $packages)
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ $packages->count() }}</div>
                <div class="stat-label">Total Packages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $packages->sum('file_count') }}</div>
                <div class="stat-label">Total Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $packages->filter(fn($p) => $p['has_composer'])->count() }}</div>
                <div class="stat-label">With Composer</div>
            </div>
        </div>

        <div class="packages">
            @forelse($packages as $package)
                <div class="package-card">
                    <div class="package-info">
                        <h3>{{ $package['full_name'] }}</h3>
                        <p>Version: {{ $package['version'] }} | Files: {{ $package['file_count'] }} | {{ $package['has_composer'] ? '✅ Composer' : '❌ No Composer' }}</p>
                    </div>
                    <div class="package-actions">
                        <a href="{{ route('packages.show', [$package['vendor'], $package['name']]) }}" class="btn btn-primary">View</a>
                        <a href="{{ route('packages.packagist', [$package['vendor'], $package['name']]) }}" class="btn btn-secondary">Packagist</a>
                    </div>
                </div>
            @empty
                <div class="empty">
                    <h3>No packages yet</h3>
                    <p>Create your first package using: <code>php artisan pack:me vendor name</code></p>
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
