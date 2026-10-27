<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | MedikaCare</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-transparan.png') }}">

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/medikacare.css') }}">

</head>
<body>
@php($user = auth()->user())

<div class="mc-shell">

{{-- Sidebar --}}
<aside class="mc-aside">
    <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar">
        <div class="offcanvas-header d-lg-none">
            <button type="button" class="btn-close"
                    data-bs-dismiss="offcanvas"
                    data-bs-target="#sidebar"
                    aria-label="Tutup menu"></button>
        </div>

        <div class="offcanvas-body p-0">
            <div class="mc-sidebar-inner">

                <a href="{{ route('dashboard') }}" class="mc-brand">
                    <img src="{{ asset('images/logo-transparan.png') }}"
                         alt="Apotek MedikaCare">
                </a>

                <nav>
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}"
                       class="mc-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        Dashboard
                    </a>

                    {{-- Inventori --}}
                    <div class="mc-section">Inventori</div>

                    <a href="{{ route('products.index') }}"
                       class="mc-link {{ request()->routeIs('products.*') && request('status') !== 'rendah' ? 'active' : '' }}">
                        <i class="bi bi-capsule"></i>
                        Produk
                    </a>

                    <a href="{{ route('products.index', ['status' => 'rendah']) }}"
                       class="mc-link {{ request()->routeIs('products.index') && request('status') === 'rendah' ? 'active' : '' }}">
                        <i class="bi bi-exclamation-triangle"></i>
                        Stok Rendah
                    </a>

                    {{-- Transaksi --}}
                    <div class="mc-section">Transaksi</div>

                    <a href="{{ route('purchases.index') }}"
                       class="mc-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-in-down"></i>
                        Pembelian
                    </a>

                    @php($pendingOrders = \App\Models\Sale::where('status', 'pending')->count())

                    <a href="{{ route('sales.index') }}"
                       class="mc-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <i class="bi bi-cart-check"></i> Penjualan
                        @if ($pendingOrders > 0)
                            <span class="nav-badge">{{ $pendingOrders }}</span>
                        @endif
                    </a>

                    {{-- Master Data khusus Admin --}}
                    @if ($user->isAdmin())
                        <div class="mc-section">Master Data</div>

                        <a href="{{ route('categories.index') }}"
                           class="mc-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            Kategori
                        </a>

                        <a href="{{ route('suppliers.index') }}"
                           class="mc-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i>
                            Supplier
                        </a>

                        {{-- Administrasi --}}
                        <div class="mc-section">Administrasi</div>

                        <a href="{{ route('reports.sales') }}"
                           class="mc-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i>
                            Laporan
                        </a>

                        <a href="{{ route('users.index') }}"
                           class="mc-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            Pengguna
                        </a>
                    @endif
                </nav>

            </div>
        </div>
    </div>
</aside>

{{-- Konten utama --}}
<div class="mc-main">

    {{-- Topbar --}}
    <header class="mc-topbar">

        {{-- Tombol menu HP --}}
        <button type="button"
                class="btn d-lg-none p-0 me-1"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebar"
                aria-label="Buka menu">
            <i class="bi bi-list fs-2"></i>
        </button>

        {{-- Judul halaman --}}
        <div class="tb-title-wrap">
            <div class="tb-ico">
                <i class="bi @yield('page_icon', 'bi-grid-1x2')"></i>
            </div>

            <div>
                <div class="tb-title">@yield('page_title')</div>
                <div class="tb-sub">
                    @yield('page_sub', 'Apotek MedikaCare')
                </div>
            </div>
        </div>

        {{-- Informasi pengguna --}}
        <div class="tb-right">

            <div class="tb-date d-none d-md-inline-flex">
                <i class="bi bi-calendar3"></i>
                {{ now()->locale('id')->translatedFormat('D, d M Y') }}
            </div>

            <div class="tb-user">
                <div class="tb-avatar">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>

                <div class="tb-meta">
                    <div class="tb-name">{{ $user->name }}</div>

                    <span class="role-badge tb-role {{ $user->isAdmin() ? 'role-admin' : '' }}">
                        {{ $user->roleLabel() }}
                    </span>
                </div>
            </div>

            <span class="tb-divider d-none d-sm-block"></span>

            {{-- Tombol keluar --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Keluar</span>
                </button>
            </form>

        </div>
    </header>

    {{-- Isi halaman --}}
    <main class="mc-content">

        @if (session('success'))
            <div class="alert alert-success rounded-4">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger rounded-4">
                <i class="bi bi-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')

    </main>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>