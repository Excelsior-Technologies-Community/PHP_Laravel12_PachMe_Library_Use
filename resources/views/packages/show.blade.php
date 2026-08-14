<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $package['full_name'] }} - PackMe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 1rem; }
        .breadcrumb { margin-bottom: 2rem; }
        .breadcrumb a { color: #6366f1; text-decoration: none; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { font-size: 1.2rem; margin-bottom: 1rem; color: #333; }
        .info-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-label { color: #666; }
        .info-value { font-weight: 500; color: #333; }
        .actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; display: inline-block; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-secondary { background: #e5e7eb; color: #333; }
        .btn-danger { background: #ef4444; color: white; }
        pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.85rem; max-height: 400px; overflow-y: auto; }
        .file-list { list-style: none; max-height: 300px; overflow-y: auto; }
        .file-list li { padding: 0.3rem 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('packages.index') }}">← Back to Dashboard</a>
        </div>

        <h1>{{ $package['full_name'] }}</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="grid">
            <div class="card">
                <h2>📋 Package Info</h2>
                <div class="info-row">
                    <span class="info-label">Vendor</span>
                    <span class="info-value">{{ $package['vendor'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value">{{ $package['name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Version</span>
                    <span class="info-value">{{ $composer['version'] ?? '0.0.1' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Files</span>
                    <span class="info-value">{{ $package['file_count'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Git</span>
                    <span class="info-value">{{ $hasGit ? '✅ Initialized' : '❌ Not initialized' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">License</span>
                    <span class="info-value">{{ $composer['license'] ?? 'MIT' }}</span>
                </div>
            </div>

            <div class="card">
                <h2>⚙️ Actions</h2>
                <div class="actions">
                    <form action="{{ route('packages.publish', [$package['vendor'], $package['name']]) }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="btn btn-success">📤 Publish All</button>
                    </form>
                    <form action="{{ route('packages.publish', [$package['vendor'], $package['name']]) }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="config">
                        <button type="submit" class="btn btn-primary">📄 Config</button>
                    </form>
                    <form action="{{ route('packages.publish', [$package['vendor'], $package['name']]) }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="views">
                        <button type="submit" class="btn btn-primary">👁️ Views</button>
                    </form>
                </div>
                <div class="actions">
                    <form action="{{ route('packages.git-init', [$package['vendor'], $package['name']]) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary" {{ $hasGit ? 'disabled' : '' }}>🔧 Init Git</button>
                    </form>
                    <a href="{{ route('packages.packagist', [$package['vendor'], $package['name']]) }}" class="btn btn-secondary">📦 Packagist Guide</a>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>📁 Files ({{ $package['file_count'] }})</h2>
                <ul class="file-list">
                    @foreach($package['files'] as $file)
                        <li>{{ $file }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="card">
                <h2>📝 README</h2>
                @if($readme)
                    <pre>{{ $readme }}</pre>
                @else
                    <p style="color: #999;">No README.md found</p>
                @endif
            </div>
        </div>

        @if($changelog)
            <div class="card">
                <h2>📊 Changelog</h2>
                <pre>{{ $changelog }}</pre>
            </div>
        @endif
    </div>
</body>
</html>
