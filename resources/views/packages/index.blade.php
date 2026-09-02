<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

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
            max-width: 1300px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 2rem;
        }

        .stats {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));

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

            font-size: 1.8rem;

            font-weight: bold;

            color: #6366f1;
        }

        .stat-label {

            color: #666;

            font-size: 0.9rem;

            margin-top: 0.5rem;
        }

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
                2fr 1fr 1fr 1fr 1fr 1fr;

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

            flex-wrap: wrap;

            margin-top: 1rem;
        }

        .btn {

            padding:
                0.55rem 1rem;

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

        .btn-danger {

            background: #ef4444;

            color: white;
        }

        .btn-warning {

            background: #f59e0b;

            color: white;
        }

        .btn:hover {

            opacity: 0.9;
        }

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

            margin-bottom: 0.7rem;
        }

        .package-info p {

            color: #666;

            font-size: 0.9rem;

            line-height: 1.7;
        }

        .package-actions {

            display: flex;

            gap: 0.5rem;

            flex-wrap: wrap;

            justify-content: flex-end;
        }

        .badges {

            display: flex;

            gap: 0.4rem;

            flex-wrap: wrap;

            margin-top: 0.7rem;
        }

        .badge {

            padding:
                0.2rem 0.55rem;

            border-radius: 20px;

            font-size: 0.75rem;

            font-weight: 600;
        }

        .badge-success {

            background: #d1fae5;

            color: #065f46;
        }

        .badge-danger {

            background: #fee2e2;

            color: #991b1b;
        }

        .badge-info {

            background: #dbeafe;

            color: #1e40af;
        }

        .badge-warning {

            background: #fef3c7;

            color: #92400e;
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

        .result-bar {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 1rem;

            gap: 1rem;

            flex-wrap: wrap;
        }

        .result-count {

            color: #666;

            font-size: 0.9rem;
        }

        .pagination-wrapper {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 0.4rem;

            margin-top: 2rem;

            flex-wrap: wrap;
        }

        .page-link {

            min-width: 38px;

            height: 38px;

            padding: 0.5rem;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 5px;

            background: white;

            border: 1px solid #ddd;

            color: #333;

            text-decoration: none;

            font-size: 0.9rem;
        }

        .page-link.active {

            background: #6366f1;

            border-color: #6366f1;

            color: white;
        }

        .page-link.disabled {

            opacity: 0.5;

            pointer-events: none;
        }

        .pagination-info {

            text-align: center;

            color: #666;

            margin-top: 0.8rem;

            font-size: 0.85rem;
        }

        .export-area {

            display: flex;

            justify-content: flex-end;

            margin-bottom: 1rem;
        }

        @media (max-width: 1100px) {

            .filter-form {

                grid-template-columns:
                    repeat(3, 1fr);
            }

        }

        @media (max-width: 800px) {

            .filter-form {

                grid-template-columns:
                    1fr 1fr;
            }

            .package-card {

                flex-direction: column;

                align-items: flex-start;
            }

            .package-actions {

                justify-content: flex-start;
            }

        }

        @media (max-width: 600px) {

            body {

                padding: 1rem;
            }

            .filter-form {

                grid-template-columns: 1fr;
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


        {{-- Success --}}

        @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

        @endif


        {{-- Error --}}

        @if(session('error'))

        <div class="alert alert-error">

            {{ session('error') }}

        </div>

        @endif


        {{-- =========================================================
         STATISTICS
    ========================================================== --}}

        <div class="stats">

            <div class="stat-card">

                <div class="stat-number">

                    {{ $totalPackages }}

                </div>

                <div class="stat-label">

                    Packages Found

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">

                    {{ $totalFiles }}

                </div>

                <div class="stat-label">

                    Total Files

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">

                    {{ $composerPackages }}

                </div>

                <div class="stat-label">

                    Composer Packages

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">

                    {{ $gitPackages }}

                </div>

                <div class="stat-label">

                    Git Packages

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">

                    {{ $readmePackages }}

                </div>

                <div class="stat-label">

                    README Packages

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-number">

                    {{ number_format($totalSize / 1024 / 1024, 2) }} MB

                </div>

                <div class="stat-label">

                    Total Size

                </div>

            </div>

        </div>


        {{-- =========================================================
         FILTERS
    ========================================================== --}}

        <div class="filter-card">

            <div class="filter-title">

                🔎 Search & Advanced Filters

            </div>


            <form
                action="{{ route('packages.index') }}"
                method="GET"
                class="filter-form">

                {{-- Search --}}

                <div class="form-group">

                    <label for="search">

                        Search

                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Vendor or package name..."
                        value="{{ $search }}">

                </div>


                {{-- Composer --}}

                <div class="form-group">

                    <label for="composer">

                        Composer

                    </label>

                    <select
                        id="composer"
                        name="composer"
                        class="form-control">

                        <option
                            value="all"
                            {{ $composerFilter === 'all'
                            ? 'selected'
                            : '' }}>
                            All
                        </option>

                        <option
                            value="yes"
                            {{ $composerFilter === 'yes'
                            ? 'selected'
                            : '' }}>
                            With Composer
                        </option>

                        <option
                            value="no"
                            {{ $composerFilter === 'no'
                            ? 'selected'
                            : '' }}>
                            Without Composer
                        </option>

                    </select>

                </div>


                {{-- Git --}}

                <div class="form-group">

                    <label for="git">

                        Git

                    </label>

                    <select
                        id="git"
                        name="git"
                        class="form-control">

                        <option
                            value="all"
                            {{ $gitFilter === 'all'
                            ? 'selected'
                            : '' }}>
                            All
                        </option>

                        <option
                            value="yes"
                            {{ $gitFilter === 'yes'
                            ? 'selected'
                            : '' }}>
                            Git Initialized
                        </option>

                        <option
                            value="no"
                            {{ $gitFilter === 'no'
                            ? 'selected'
                            : '' }}>
                            No Git
                        </option>

                    </select>

                </div>


                {{-- README --}}

                <div class="form-group">

                    <label for="readme">

                        README

                    </label>

                    <select
                        id="readme"
                        name="readme"
                        class="form-control">

                        <option
                            value="all"
                            {{ $readmeFilter === 'all'
                            ? 'selected'
                            : '' }}>
                            All
                        </option>

                        <option
                            value="yes"
                            {{ $readmeFilter === 'yes'
                            ? 'selected'
                            : '' }}>
                            Has README
                        </option>

                        <option
                            value="no"
                            {{ $readmeFilter === 'no'
                            ? 'selected'
                            : '' }}>
                            No README
                        </option>

                    </select>

                </div>


                {{-- Sort --}}

                <div class="form-group">

                    <label for="sort">

                        Sort

                    </label>

                    <select
                        id="sort"
                        name="sort"
                        class="form-control">

                        <option
                            value="name_asc"
                            {{ $sort === 'name_asc'
                            ? 'selected'
                            : '' }}>
                            Name A-Z
                        </option>

                        <option
                            value="name_desc"
                            {{ $sort === 'name_desc'
                            ? 'selected'
                            : '' }}>
                            Name Z-A
                        </option>

                        <option
                            value="files_asc"
                            {{ $sort === 'files_asc'
                            ? 'selected'
                            : '' }}>
                            Files Low-High
                        </option>

                        <option
                            value="files_desc"
                            {{ $sort === 'files_desc'
                            ? 'selected'
                            : '' }}>
                            Files High-Low
                        </option>

                        <option
                            value="size_asc"
                            {{ $sort === 'size_asc'
                            ? 'selected'
                            : '' }}>
                            Size Low-High
                        </option>

                        <option
                            value="size_desc"
                            {{ $sort === 'size_desc'
                            ? 'selected'
                            : '' }}>
                            Size High-Low
                        </option>

                        <option
                            value="version"
                            {{ $sort === 'version'
                            ? 'selected'
                            : '' }}>
                            Version
                        </option>

                    </select>

                </div>


                {{-- Per Page --}}

                <div class="form-group">

                    <label for="per_page">

                        Per Page

                    </label>

                    <select
                        id="per_page"
                        name="per_page"
                        class="form-control">

                        @foreach([5, 10, 20, 50] as $number)

                        <option
                            value="{{ $number }}"
                            {{ $perPage == $number
                                ? 'selected'
                                : '' }}>
                            {{ $number }}
                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="filter-buttons">

                    <button
                        type="submit"
                        class="btn btn-primary">
                        🔎 Apply Filters
                    </button>

                    <a
                        href="{{ route('packages.index') }}"
                        class="btn btn-danger">
                        Reset
                    </a>

                </div>

            </form>

        </div>


        {{-- =========================================================
         EXPORT
    ========================================================== --}}

        <div class="export-area">

            <a
                href="{{ route(
                'packages.export',
                request()->query()
            ) }}"
                class="btn btn-success">
                📊 Export CSV
            </a>

        </div>


        {{-- =========================================================
         RESULT COUNT
    ========================================================== --}}

        <div class="result-bar">

            <div class="result-count">

                Showing

                <strong>
                    {{ $paginatedPackages->count() }}
                </strong>

                of

                <strong>
                    {{ $total }}
                </strong>

                package(s)

                @if($search)

                matching

                <strong>
                    "{{ $search }}"
                </strong>

                @endif

            </div>

        </div>


        {{-- =========================================================
         PACKAGE LIST
    ========================================================== --}}

        <div class="packages">

            @forelse($paginatedPackages as $package)

            <div class="package-card">

                <div class="package-info">

                    <h3>

                        📦
                        {{ $package['full_name'] }}

                    </h3>

                    <p>

                        Version:
                        {{ $package['version'] }}

                        |

                        Files:
                        {{ $package['file_count'] }}

                        |

                        Size:
                        {{ $package['size'] }}

                    </p>


                    <div class="badges">

                        @if($package['has_composer'])

                        <span class="badge badge-success">

                            ✅ Composer

                        </span>

                        @else

                        <span class="badge badge-danger">

                            ❌ No Composer

                        </span>

                        @endif


                        @if($package['has_git'])

                        <span class="badge badge-info">

                            🔧 Git

                        </span>

                        @else

                        <span class="badge badge-danger">

                            ❌ No Git

                        </span>

                        @endif


                        @if($package['has_readme'])

                        <span class="badge badge-success">

                            📖 README

                        </span>

                        @else

                        <span class="badge badge-warning">

                            ⚠️ No README

                        </span>

                        @endif

                    </div>

                </div>


                <div class="package-actions">

                    <a
                        href="{{ route(
                            'packages.show',
                            [
                                $package['vendor'],
                                $package['name']
                            ]
                        ) }}"
                        class="btn btn-primary">
                        View
                    </a>


                    <a
                        href="{{ route(
                            'packages.packagist',
                            [
                                $package['vendor'],
                                $package['name']
                            ]
                        ) }}"
                        class="btn btn-secondary">
                        Packagist
                    </a>


                    <a
                        href="{{ route(
                            'packages.download',
                            [
                                $package['vendor'],
                                $package['name']
                            ]
                        ) }}"
                        class="btn btn-success">
                        ⬇️ ZIP
                    </a>


                    {{-- DELETE --}}

                    <form
                        action="{{ route(
                            'packages.destroy',
                            [
                                $package['vendor'],
                                $package['name']
                            ]
                        ) }}"
                        method="POST"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to delete {{ $package['full_name'] }}?'
                            );
                        ">

                        @csrf

                        @method('DELETE')

                        <button
                            type="submit"
                            class="btn btn-danger">
                            🗑️ Delete
                        </button>

                    </form>

                </div>

            </div>

            @empty

            <div class="empty">

                <h3>

                    No packages found

                </h3>

                <br>

                @if($search)

                <p>

                    No package matches
                    "{{ $search }}".

                </p>

                @else

                <p>

                    Create your first package:

                </p>

                <br>

                <code>

                    php artisan pack:me vendor name

                </code>

                @endif

            </div>

            @endforelse

        </div>


        {{-- =========================================================
         PAGINATION
    ========================================================== --}}

        @if($lastPage > 1)

        <div class="pagination-wrapper">

            {{-- Previous --}}

            @if($currentPage > 1)

            <a
                class="page-link"
                href="{{ request()->fullUrlWithQuery([
                        'page' => $currentPage - 1
                    ]) }}">
                ←
            </a>

            @else

            <span class="page-link disabled">
                ←
            </span>

            @endif


            {{-- Numbers --}}

            @for(
            $page = 1;
            $page <= $lastPage;
                $page++
                )

                <a
                class="page-link
                        {{ $page == $currentPage
                            ? 'active'
                            : '' }}"
                href="{{ request()->fullUrlWithQuery([
                        'page' => $page
                    ]) }}">
                {{ $page }}
                </a>

                @endfor


                {{-- Next --}}

                @if($currentPage < $lastPage)

                    <a
                    class="page-link"
                    href="{{ request()->fullUrlWithQuery([
                        'page' => $currentPage + 1
                    ]) }}">
                    →
                    </a>

                    @else

                    <span class="page-link disabled">
                        →
                    </span>

                    @endif

        </div>


        <div class="pagination-info">

            Page
            <strong>{{ $currentPage }}</strong>
            of
            <strong>{{ $lastPage }}</strong>

        </div>

        @endif

    </div>

</body>

</html>