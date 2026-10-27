@extends('layouts.app')

@section('title', 'Produk')
@section('page_title', 'Produk')
@section('page_icon', 'bi-capsule')
@section('page_sub', 'Obat dan alat kesehatan')

@section('content')
    <div class="mc-card reveal">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
           @php
    $base = array_filter([
        'q' => request('q'),
        'category_id' => request('category_id'),
    ]);
@endphp

            <div class="pill-tabs">
                <a href="{{ route('products.index', $base) }}"
                   class="pill-tab {{ request('status') ? '' : 'active' }}">
                    Semua <span class="cnt">{{ $countAll }}</span>
                </a>

                <a href="{{ route('products.index', $base + ['status' => 'rendah']) }}"
                   class="pill-tab {{ request('status') === 'rendah' ? 'active' : '' }}">
                    Stok rendah <span class="cnt">{{ $countLow }}</span>
                </a>

                <a href="{{ route('products.index', $base + ['status' => 'habis']) }}"
                   class="pill-tab {{ request('status') === 'habis' ? 'active' : '' }}">
                    Habis <span class="cnt">{{ $countOut }}</span>
                </a>
            </div>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn btn-mc">
                    <i class="bi bi-plus-lg"></i> Tambah Produk
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="row g-2 mb-4">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control"
                           placeholder="Cari SKU atau nama produk...">
                </div>
            </div>

            <div class="col-lg-4">
                <select name="category_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-mc flex-fill">Cari</button>
                <a href="{{ route('products.index') }}"
                   class="btn btn-soft d-inline-flex align-items-center text-decoration-none">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $max = max($product->minimum_stock * 2, 1);
                            $pct = min(100, (int) round($product->stock / $max * 100));
                        @endphp

                        <tr>
                            <td>
                                <span class="sku-chip">{{ $product->sku }}</span>
                            </td>

                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <div class="small text-muted">
                                    {{ $product->category->name ?? 'Tanpa kategori' }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </div>
                                <div class="small text-muted">
                                    Beli Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="stock-cell">
                                <div class="fw-bold">
                                    {{ $product->stock }}
                                    <span class="small text-muted fw-normal">
                                        / min {{ $product->minimum_stock }}
                                    </span>
                                </div>

                                <div class="stock-bar">
                                    <span class="sb-{{ $product->stock_status }}"
                                          style="width: {{ $pct }}%"></span>
                                </div>
                            </td>

                            <td>
                                @include('products._status')
                            </td>

                            <td class="text-end text-nowrap">
                                <a href="{{ route('products.show', $product) }}"
                                   class="btn-soft text-decoration-none">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="btn-soft text-decoration-none">
                                        <i class="bi bi-pencil"></i> Ubah
                                    </a>

                                    <form method="POST"
                                          action="{{ route('products.destroy', $product) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-soft text-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-box">
                                    <i class="bi bi-search"></i>
                                    Tidak ada produk yang cocok dengan pencarian.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
@endsection
