<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Grocery POS')</title>
    <script>
        (function () {
            const saved = localStorage.getItem('pos-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', saved);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-50:  #eef2ff;
            --brand-100: #e0e7ff;
            --brand-200: #c7d2fe;
            --brand-500: #6366f1;
            --brand-600: #4f46e5;
            --brand-700: #4338ca;
            --brand-900: #1e1b4b;
            --ink-900: #0f172a;
            --ink-600: #475569;
            --ink-400: #94a3b8;
            --surface: #f6f6fb;
            --card-bg: #ffffff;
            --border-c: #ececf5;
        }
        html[data-bs-theme="dark"] {
            --surface: #0d1117;
            --card-bg: #161b22;
            --border-c: #30363d;
            --ink-900: #e6edf3;
            --ink-600: #9198a1;
            --ink-400: #7d8590;
        }
        * { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        html, body { background: var(--surface); color: var(--ink-900); }
        body { min-height: 100vh; }

        .sidebar { background: linear-gradient(180deg, var(--brand-900) 0%, #14123a 100%) !important; width: 264px; }
        .sidebar a {
            color: #c7c9f7; text-decoration: none; display: flex; align-items: center;
            padding: .7rem 1.1rem; border-radius: .65rem; margin: .18rem .65rem;
            font-weight: 500; font-size: .92rem; transition: background .15s ease, color .15s ease;
        }
        .sidebar a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar a.active {
            background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
            color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,.35);
        }
        .sidebar .brand { color: #fff; font-weight: 800; font-size: 1.2rem; letter-spacing: -.02em; }
        .sidebar .brand i { color: var(--brand-500); }

        .topbar { background: var(--card-bg); border-bottom: 1px solid var(--border-c); box-shadow: 0 1px 2px rgba(15,23,42,.03); }
        .content-area { padding: 1.75rem; }
        .hamburger-btn, .theme-toggle-btn {
            border: none; background: var(--brand-50); font-size: 1.25rem;
            color: var(--brand-700); padding: .4rem .65rem; border-radius: .6rem;
        }
        .hamburger-btn:hover, .theme-toggle-btn:hover { background: var(--brand-100); }
        @media (max-width: 991.98px) { .content-area { padding: 1.1rem; } }

        .card {
            background-color: var(--card-bg); color: var(--ink-900);
            border: 1px solid var(--border-c); border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(15,23,42,.04), 0 1px 2px rgba(15,23,42,.03);
        }
        .stat-card h3, .stat-card h4 { font-weight: 800; letter-spacing: -.02em; }

        .btn { border-radius: .6rem; font-weight: 600; font-size: .9rem; }
        .btn-dark { background: linear-gradient(135deg, var(--brand-500), var(--brand-700)); border: none; color: #fff; }
        .btn-dark:hover, .btn-dark:focus { background: linear-gradient(135deg, var(--brand-600), var(--brand-900)); color: #fff; }
        .btn-outline-dark { color: var(--brand-700); border-color: var(--brand-200); }
        .btn-outline-dark:hover { background: var(--brand-50); border-color: var(--brand-500); color: var(--brand-700); }
        .btn-outline-secondary { border-color: var(--border-c); color: var(--ink-600); }
        .btn-outline-primary { color: var(--brand-600); border-color: var(--brand-200); }
        .btn-outline-primary:hover { background: var(--brand-50); border-color: var(--brand-500); color: var(--brand-700); }

        .form-control, .form-select {
            border-radius: .6rem; border-color: var(--border-c);
            background-color: var(--card-bg); color: var(--ink-900);
        }
        .form-control:focus, .form-select:focus { border-color: var(--brand-500); box-shadow: 0 0 0 .2rem rgba(99,102,241,.15); }

        .badge { font-weight: 600; border-radius: .5rem; padding: .38em .65em; }
        .badge.bg-success { background-color: #d1fae5 !important; color: #065f46 !important; }
        .badge.bg-danger  { background-color: #fee2e2 !important; color: #991b1b !important; }
        .badge.bg-warning { background-color: #fef3c7 !important; color: #92400e !important; }
        .badge.bg-secondary { background-color: #f1f2f8 !important; color: var(--ink-600) !important; }
        .badge.bg-primary { background-color: var(--brand-100) !important; color: var(--brand-700) !important; }
        .badge.bg-dark { background-color: #e2e8f0 !important; color: #1e293b !important; }
        .badge.bg-info { background-color: var(--brand-100) !important; color: var(--brand-700) !important; }

        .table { color: var(--ink-900); }
        .table thead th {
            color: var(--ink-400); font-size: .72rem; text-transform: uppercase;
            letter-spacing: .04em; font-weight: 700; border-bottom: 1px solid var(--border-c); background-color: transparent;
        }
        .table td { vertical-align: middle; border-color: var(--border-c); }
        .table-hover tbody tr:hover { background-color: var(--brand-50); color: var(--ink-900); }

        code { background: var(--brand-50); color: var(--brand-700); padding: .15em .5em; border-radius: .4rem; font-size: .82em; }
        .text-muted { color: var(--ink-600) !important; }

        html[data-bs-theme="dark"] .table-hover tbody tr:hover { background-color: #1c2128; }
        html[data-bs-theme="dark"] code { background: #1c2128; color: #a5b4fc; }
        html[data-bs-theme="dark"] .hamburger-btn,
        html[data-bs-theme="dark"] .theme-toggle-btn { background: #1c2128; color: #a5b4fc; }
        html[data-bs-theme="dark"] .hamburger-btn:hover,
        html[data-bs-theme="dark"] .theme-toggle-btn:hover { background: #262c36; }

        html[data-bs-theme="dark"] .badge.bg-success { background-color: #10261c !important; color: #3fb950 !important; }
        html[data-bs-theme="dark"] .badge.bg-danger  { background-color: #2d1214 !important; color: #f85149 !important; }
        html[data-bs-theme="dark"] .badge.bg-warning { background-color: #2b1d05 !important; color: #d29922 !important; }
        html[data-bs-theme="dark"] .badge.bg-secondary { background-color: #21262d !important; color: var(--ink-600) !important; }
        html[data-bs-theme="dark"] .badge.bg-primary { background-color: #1c1f3d !important; color: #a5b4fc !important; }
        html[data-bs-theme="dark"] .badge.bg-dark { background-color: #21262d !important; color: #c9d1d9 !important; }
        html[data-bs-theme="dark"] .badge.bg-info { background-color: #1c1f3d !important; color: #a5b4fc !important; }

        html[data-bs-theme="dark"] .alert-success { background: #10261c !important; color: #3fb950 !important; }
        html[data-bs-theme="dark"] .alert-danger { background: #2d1214 !important; color: #f85149 !important; }
        html[data-bs-theme="dark"] .btn-close { filter: invert(1); }

        @media print { .no-print { display: none !important; } }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="sidebar offcanvas offcanvas-start d-flex flex-column p-2 no-print" tabindex="-1" id="sidebarMenu">
        <div class="d-flex justify-content-between align-items-center px-2 pt-2 pb-3">
            <span class="brand"><i class="bi bi-basket3-fill"></i> Grocery POS</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>
        @if(auth()->user()?->isAdmin())
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        @endif
        <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}"><i class="bi bi-upc-scan me-2"></i>POS Checkout</a>
        @if(auth()->user()?->isAdmin())
            <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index') ? 'active' : '' }}"><i class="bi bi-receipt me-2"></i>Sales History</a>
        @else
            <a href="{{ route('sales.my') }}" class="{{ request()->routeIs('sales.my') ? 'active' : '' }}"><i class="bi bi-receipt me-2"></i>My Transactions</a>
        @endif
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box-seam me-2"></i>Products</a>
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i>Customers / Credit</a>
        @if(auth()->user()?->isAdmin())
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
            <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"><i class="bi bi-clock-history me-2"></i>Audit Log</a>
        @endif
        <div class="mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100 mx-2" style="width:calc(100% - 1rem)"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
            </form>
        </div>
    </nav>

    <div class="topbar d-flex justify-content-between align-items-center px-3 px-lg-4 py-2 no-print">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                <i class="bi bi-list"></i>
            </button>
            <div class="fw-bold" style="letter-spacing:-.01em;">@yield('page-title', 'Dashboard')</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="theme-toggle-btn no-print" type="button" id="themeToggleBtn" title="Toggle dark mode">
                <i class="bi bi-moon-stars-fill" id="themeToggleIcon"></i>
            </button>
            <div class="text-muted small text-end">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name ?? '' }}
                <span class="badge bg-primary text-uppercase ms-1">{{ auth()->user()->role ?? '' }}</span>
            </div>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success no-print" style="border-radius:.75rem; border:none;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger no-print" style="border-radius:.75rem; border:none;">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeToggleIcon = document.getElementById('themeToggleIcon');

    function updateThemeIcon(theme) {
        themeToggleIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }

    updateThemeIcon(document.documentElement.getAttribute('data-bs-theme') || 'light');

    themeToggleBtn.addEventListener('click', function () {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('pos-theme', next);
        updateThemeIcon(next);
    });
</script>
@stack('scripts')
</body>
</html>
