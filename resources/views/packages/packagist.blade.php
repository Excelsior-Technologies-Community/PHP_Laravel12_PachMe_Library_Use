<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $package['full_name'] }} - Packagist Guide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 1rem; }
        .breadcrumb { margin-bottom: 2rem; }
        .breadcrumb a { color: #6366f1; text-decoration: none; }
        .step { background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #6366f1; }
        .step h3 { color: #333; margin-bottom: 0.5rem; }
        .step p { color: #666; margin-bottom: 1rem; }
        code { background: #1e293b; color: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 4px; font-family: monospace; }
        pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 4px; overflow-x: auto; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; display: inline-block; background: #6366f1; color: white; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('packages.show', [$package['vendor'], $package['name']]) }}">← Back to Package</a>
        </div>

        <h1>📦 Packagist Submission Guide</h1>

        <div class="alert">
            <strong>Package:</strong> {{ $package['full_name'] }}<br>
            <strong>Path:</strong> {{ $package['path'] }}
        </div>

        <div class="step">
            <h3>Step 1: Push to GitHub</h3>
            <p>Make sure your package is pushed to a public GitHub repository.</p>
            <pre>cd {{ $package['path'] }}
git remote add origin https://github.com/your-username/{{ $package['name'] }}.git
git branch -M main
git push -u origin main</pre>
        </div>

        <div class="step">
            <h3>Step 2: Submit to Packagist</h3>
            <p>Go to Packagist and submit your repository URL.</p>
            <a href="{{ $packagistUrl }}" target="_blank" class="btn">Submit to Packagist</a>
        </div>

        <div class="step">
            <h3>Step 3: Setup Webhook (Recommended)</h3>
            <p>On Packagist, go to your package settings and add a GitHub webhook to auto-update when you push.</p>
        </div>

        <div class="step">
            <h3>Step 4: Install Your Package</h3>
            <p>Users can now install your package via Composer:</p>
            <pre>composer require {{ $package['full_name'] }}</pre>
        </div>

        <div class="step">
            <h3>Step 5: Publish Assets</h3>
            <p>Users should publish the package assets:</p>
            <pre>php artisan vendor:publish --provider="{{ $package['full_name'] }}"</pre>
        </div>
    </div>
</body>
</html>
