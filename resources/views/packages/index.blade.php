<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>PackMe Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                sans-serif;

            background: #f5f5f5;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 2rem;
        }

        .stats {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(200px, 1fr));

            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;

            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #6366f1;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /*
        |--------------------------------------------------------------------------
        | Search / Filters
        |--------------------------------------------------------------------------
        */

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;

            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.1);

            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;

            margin-bottom: 1rem;
        }

        .filter-form {
            display: grid;

            grid-template-columns:
                2fr 1fr 1fr auto;

            gap: 1rem;

            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.85rem;
            color: #555;
            font-weight: 600;
        }

        .form-control {
            width: 100%;

            padding: 0.65rem 0.8rem;

            border: 1px solid #d1d5db;
            border-radius: 5px;

            font-size: 0.9rem;

            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #6366f1;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */

        .packages {
            display: grid;
            gap: 1rem;
        }

        .package-card {
            background: white;

            padding: 1.5rem;

            border-radius: 8px;

            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.1);

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 1rem;
        }

        .package-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .package-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .package-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.55rem 1rem;

            border: none;

            border-radius: 4px;

            cursor: pointer;

            text-decoration: none;

            font-size: 0.9rem;

            display: inline-block;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-reset {
            background: #ef4444;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .empty {
            text-align: center;

            padding: 3rem;

            color: #666;

            background: white;

            border-radius: 8px;
        }

        .alert {
            padding: 1rem;

            border-radius: 4px;

            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .result-count {
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 900px) {

            .filter-form {
                grid-template-columns: 1fr;
            }

            .package-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {

            body {
                padding: 1rem;
            }

            .package-actions {
                width: 100%;
            }

            .package-actions .btn {
                flex: 1;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>📦 PackMe Dashboard</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif


    @php
        $packages = is_array($packages)
            ? collect($packages)
            : $packages;
    @endphp


    <!-- Statistics -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-number">
                {{ $packages->count() }}
            </div>

            <div class="stat-label">
                Packages Found
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                {{ $packages->sum('file_count') }}
            </div>

            <div class="stat-label">
                Total Files
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                {{
                    $packages
                        ->filter(fn($p) => $p['has_composer'])
                        ->count()
                }}
            </div>

            <div class="stat-label">
                With Composer
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                {{
                    $packages
                        ->filter(fn($p) => !$p['has_composer'])
                        ->count()
                }}
            </div>

            <div class="stat-label">
                Without Composer
            </div>

        </div>

    </div>


    <!-- Search & Filters -->

    <div class="filter-card">

        <div class="filter-title">
            🔎 Search & Filter Packages
        </div>

        <form
            action="{{ route('packages.index') }}"
            method="GET"
            class="filter-form"
        >

            <!-- Search -->

            <div class="form-group">

                <label for="search">
                    Search Package
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    placeholder="Search vendor or package name..."
                    value="{{ $search }}"
                >

            </div>


            <!-- Composer Filter -->

            <div class="form-group">

                <label for="composer">
                    Composer
                </label>

                <select
                    id="composer"
                    name="composer"
                    class="form-control"
                >

                    <option
                        value="all"
                        {{ $composerFilter === 'all' ? 'selected' : '' }}
                    >
                        All Packages
                    </option>

                    <option
                        value="yes"
                        {{ $composerFilter === 'yes' ? 'selected' : '' }}
                    >
                        With Composer
                    </option>

                    <option
                        value="no"
                        {{ $composerFilter === 'no' ? 'selected' : '' }}
                    >
                        Without Composer
                    </option>

                </select>

            </div>


            <!-- Sorting -->

            <div class="form-group">

                <label for="sort">
                    Sort By
                </label>

                <select
                    id="sort"
                    name="sort"
                    class="form-control"
                >

                    <option
                        value="name_asc"
                        {{ $sort === 'name_asc' ? 'selected' : '' }}
                    >
                        Name A-Z
                    </option>

                    <option
                        value="name_desc"
                        {{ $sort === 'name_desc' ? 'selected' : '' }}
                    >
                        Name Z-A
                    </option>

                    <option
                        value="files_asc"
                        {{ $sort === 'files_asc' ? 'selected' : '' }}
                    >
                        Files Low-High
                    </option>

                    <option
                        value="files_desc"
                        {{ $sort === 'files_desc' ? 'selected' : '' }}
                    >
                        Files High-Low
                    </option>

                    <option
                        value="version"
                        {{ $sort === 'version' ? 'selected' : '' }}
                    >
                        Version
                    </option>

                </select>

            </div>


            <!-- Buttons -->

            <div class="filter-buttons">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    🔎 Apply
                </button>

                <a
                    href="{{ route('packages.index') }}"
                    class="btn btn-reset"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>


    <div class="result-count">

        Showing
        <strong>{{ $packages->count() }}</strong>
        package(s)

        @if($search)
            matching
            <strong>"{{ $search }}"</strong>
        @endif

    </div>


    <!-- Package List -->

    <div class="packages">

        @forelse($packages as $package)

            <div class="package-card">

                <div class="package-info">

                    <h3>
                        📦 {{ $package['full_name'] }}
                    </h3>

                    <p>
                        Version:
                        {{ $package['version'] }}

                        |

                        Files:
                        {{ $package['file_count'] }}

                        |

                        {{
                            $package['has_composer']
                                ? '✅ Composer'
                                : '❌ No Composer'
                        }}
                    </p>

                </div>


                <div class="package-actions">

                    <a
                        href="{{ route(
                            'packages.show',
                            [$package['vendor'], $package['name']]
                        ) }}"
                        class="btn btn-primary"
                    >
                        View
                    </a>


                    <a
                        href="{{ route(
                            'packages.packagist',
                            [$package['vendor'], $package['name']]
                        ) }}"
                        class="btn btn-secondary"
                    >
                        Packagist
                    </a>


                    <a
                        href="{{ route(
                            'packages.download',
                            [$package['vendor'], $package['name']]
                        ) }}"
                        class="btn btn-success"
                    >
                        ⬇️ ZIP
                    </a>

                </div>

            </div>

        @empty

            <div class="empty">

                <h3>
                    No packages found
                </h3>

                @if($search)

                    <p>
                        No package matches
                        "{{ $search }}".
                    </p>

                @else

                    <p>
                        Create your first package using:
                    </p>

                    <br>

                    <code>
                        php artisan pack:me vendor name
                    </code>

                @endif

            </div>

        @endforelse

    </div>

</div>

</body>
</html>