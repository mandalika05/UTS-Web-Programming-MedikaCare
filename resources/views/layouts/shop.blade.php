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
<body class="shop-body">

@php
$user = auth()->user();
$isCustomer = $user && $user->isCustomer();
$cartCount = $isCustomer ? count(session('cart', [])) : 0;
@endphp

<nav class="shop-nav">
    <div class="container shop-nav-inner">
        <a href="{{ route('shop.index') }}" class="shop-brand">
            <img src="{{ asset('images/logo-transparan.png') }}" alt="Apotek MedikaCare">
        </a>

    <div class="shop-links">
        <a href="{{ route('shop.index') }}"
           class="shop-link {{ request()->routeIs('shop.*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Katalog
        </a>

        @if ($isCustomer)
            <a href="{{ route('cart.index') }}"
               class="shop-link {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                <i class="bi bi-bag"></i> Keranjang

                @if ($cartCount > 0)
                    <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>

            <a href="{{ route('orders.index') }}"
               class="shop-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Pesanan Saya
            </a>
        @endif
    </div>

    <div class="shop-user">
        @if ($isCustomer)
            <div class="tb-user">
                <div class="tb-avatar">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>

                <div class="tb-meta">
                    <div class="tb-name">{{ $user->name }}</div>
                    <span class="role-badge tb-role">{{ $user->roleLabel() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Keluar</span>
                </button>
            </form>

        @elseif ($user)
            <a href="{{ route('dashboard') }}" class="btn-soft text-decoration-none">
                <i class="bi bi-speedometer2"></i> Ke dashboard
            </a>

        @else
            <a href="{{ route('login') }}" class="btn-soft text-decoration-none">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-mc" style="padding:.5rem 1.1rem">Daftar</a>
        @endif
    </div>
</div>

</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success rounded-4">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

@if (session('error'))
    <div class="alert alert-danger rounded-4">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger rounded-4">
        <ul class="mb-0 ps-3">
            @foreach (array_unique($errors->all()) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@yield('content')

</main>

<footer class="shop-footer">
    Apotek MedikaCare &middot; Sehat Bersama, Lebih Baik
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>