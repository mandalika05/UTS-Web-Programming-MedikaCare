@extends('layouts.shop')

@section('title', 'Katalog')

@section('content')
<div class="hero-banner shop-hero mb-4 reveal">
    <div class="w-100">
        <div class="small opacity-75 mb-1">
            @auth
                Halo, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}
            @else
                Selamat datang di Apotek MedikaCare
            @endauth
        </div>

        <h2 class="fw-bold mb-1">Cari obat dan alat kesehatan</h2>
        <p class="opacity-75 mb-3">
            Pesan dari rumah, lalu bayar di apotek atau lewat transfer.
        </p>

        <form method="GET" action="{{ route('shop.index') }}" class="shop-search">
            @if (request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif

            <i class="bi bi-search"></i>
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                maxlength="100"
                placeholder="Cari nama produk..."
            >
            <button type="submit">Cari</button>
        </form>
    </div>
</div>

@php
    $keep = array_filter(['q' => request('q')]);
@endphp

<div class="pill-tabs mb-4">
    <a
        href="{{ route('shop.index', $keep) }}"
        class="pill-tab {{ request('category_id') ? '' : 'active' }}"
    >
        Semua
    </a>

    @foreach ($categories as $category)
        <a
            href="{{ route('shop.index', $keep + ['category_id' => $category->id]) }}"
            class="pill-tab {{ (string) request('category_id') === (string) $category->id ? 'active' : '' }}"
        >
            {{ $category->name }}
        </a>
    @endforeach
</div>

<div class="row g-3">
    @forelse ($products as $product)
        @php
            $categoryName = $product->category->name ?? '';

            $icon = match ($categoryName) {
                'Obat Bebas' => 'bi-capsule',
                'Obat Keras' => 'bi-capsule-pill',
                'Vitamin & Suplemen' => 'bi-lightning-charge',
                'Alat Kesehatan' => 'bi-heart-pulse',
                default => 'bi-bag-plus',
            };
        @endphp

        <div class="col-sm-6 col-lg-4 col-xl-3 reveal">
            <div class="shop-card">
                <div class="badge-wrap">
                    @if ($product->stock_status === 'habis')
                        <span class="badge-s b-out">Habis</span>
                    @elseif ($product->stock_status === 'rendah')
                        <span class="badge-s b-low">Stok terbatas</span>
                    @else
                        <span class="badge-s b-ok">Tersedia</span>
                    @endif
                </div>

                <div class="shop-ico">
                    <i class="bi {{ $icon }}"></i>
                </div>

                <div class="shop-name">{{ $product->name }}</div>
                <div class="small text-muted">{{ $categoryName }}</div>

                <div class="shop-price">
                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                </div>

                @auth
                    @if (auth()->user()->isCustomer())
                        <form method="POST" action="{{ route('cart.add') }}" class="shop-add">
                            @csrf

                            <input
                                type="hidden"
                                name="product_id"
                                value="{{ $product->id }}"
                            >

                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="{{ max($product->stock, 1) }}"
                                class="form-control qty"
                                @disabled($product->stock < 1)
                            >

                            <button
                                type="submit"
                                class="btn btn-mc"
                                @disabled($product->stock < 1)
                            >
                                <i class="bi bi-bag-plus"></i> Tambah
                            </button>
                        </form>
                    @else
                        <div class="small text-muted mt-auto">
                            Pemesanan khusus akun pelanggan.
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-mc mt-auto">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Masuk untuk memesan
                    </a>
                @endauth
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="mc-card">
                <div class="empty-box">
                    <i class="bi bi-search"></i>
                    Produk yang dicari belum ditemukan.
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection