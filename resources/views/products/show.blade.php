@extends('layouts.app')

@section('title', $product->name)
@section('page_title', 'Detail Produk')
@section('page_icon', 'bi-capsule')
@section('page_sub', $product->sku)

@section('content')
    <div class="hero-banner mb-4 reveal">
        <div>
            <span class="sku-chip mb-2">{{ $product->sku }}</span>
            <h3 class="fw-bold mb-1">{{ $product->name }}</h3>
            <p class="mb-0 opacity-75">
                {{ $product->category->name ?? 'Tanpa kategori' }}
            </p>
        </div>

        <div class="health-pill">
            <div class="health-num">{{ $product->stock }}</div>
            <div class="small">stok saat ini</div>
        </div>
    </div>

    <div class="mc-card reveal" style="animation-delay: .1s">
        <div class="info-grid mb-4">
            <div class="info-item">
                <div class="lbl">Harga beli</div>
                <div class="val">
                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                </div>
            </div>

            <div class="info-item">
                <div class="lbl">Harga jual</div>
                <div class="val">
                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                </div>
            </div>

            <div class="info-item">
                <div class="lbl">Selisih per unit</div>
                <div class="val">
                    Rp {{ number_format($product->selling_price - $product->purchase_price, 0, ',', '.') }}
                </div>
            </div>

            <div class="info-item">
                <div class="lbl">Stok minimum</div>
                <div class="val">{{ $product->minimum_stock }}</div>
            </div>

            <div class="info-item">
                <div class="lbl">Status stok</div>
                <div class="val">
                    @include('products._status')
                </div>
            </div>

            <div class="info-item">
                <div class="lbl">Dipakai di transaksi</div>
                <div class="val">
                    {{ $product->purchase_items_count + $product->sale_items_count }}
                    <span class="small text-muted fw-normal">kali</span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}"
               class="btn btn-soft d-inline-flex align-items-center text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-mc">
                    <i class="bi bi-pencil"></i> Ubah
                </a>
            @endif
        </div>
    </div>
@endsection
