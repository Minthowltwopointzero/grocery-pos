<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Grocery POS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; }
        .sidebar { min-height: 100vh; background:#1e293b !important; width:230px; }
        .sidebar a { color:#cbd5e1; text-decoration:none; display:block; padding:.65rem 1.25rem; border-radius:.4rem; margin:.15rem .6rem; }
        .sidebar a:hover, .sidebar a.active { background:#334155; color:#fff; }
        .sidebar .brand { color:#fff; font-weight:700; padding:1rem 1.25rem; font-size:1.15rem; }
        .topbar { background:#fff; border-bottom:1px solid #e2e8f0; }
        .card { border:none; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .stat-card h3 { font-weight:700; }
        .content-area { padding: 1.5rem; }
        @media (max-width: 991.98px) {
            .content-area { padding: 1rem; }
        }
        @media print {
            .no-print { display:none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <nav class="sidebar offcanvas-lg offcanvas-start d-flex flex-column p-2 no-print" tabindex="-1" id="sidebarMenu">
        <div class="d-flex justify-content-between align-items-center d-lg-none px-2 pt-2">
            <span class="text-white fw-bold"><i class="bi bi-basket3-fill"></i> Grocery POS</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>
        <div class="brand d-none d-lg-block"><i class="bi bi-basket3-fill"></i> Grocery POS</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}"><i class="bi bi-upc-scan me-2"></i>POS Checkout</a>
        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><i class="bi bi-receipt me-2"></i>Sales History</a>
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box-seam me-2"></i>Products</a>
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i>Customers / Credit</a>
        <div class="mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100 mx-2" style="width:calc(100% - 1rem)"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
            </form>
        </div>
    </nav>

    <div class="flex-grow-1" style="min-width:0;">
        <div class="topbar d-flex justify-content-between align-items-center px-3 px-lg-4 py-2 no-print">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                    <i class="bi bi-list"></i>
                </button>
                <div class="fw-semibold">@yield('page-title', 'Dashboard')</div>
            </div>
            <div class="text-muted small text-end">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name ?? '' }}
                <span class="badge bg-secondary text-uppercase ms-1">{{ auth()->user()->role ?? '' }}</span>
            </div>
        </div>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success no-print">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger no-print">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Global CSRF setup for fetch() calls
    window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
@stack('scripts')
</body>
</html>