@extends('layouts.app')

@section('title', $sale->sale_number)
@section('page_title', 'Detail Penjualan')
@section('page_icon', 'bi-receipt')
@section('page_sub', $sale->sale_number)

@section('content')
@php
$when = $sale->sold_at ?? $sale->created_at;
$isTransfer = $sale->payment_method === 'transfer';
$isPending = $sale->status === 'pending';
$isCompleted = $sale->status === 'completed';
$isCancelled = $sale->status === 'cancelled';
@endphp

{{-- Ringkasan transaksi --}}
<div class="hero-banner mb-4 reveal">
    <div>
        <span class="sku-chip mb-2">{{ $sale->sale_number }}</span>

        <h3 class="fw-bold mb-1">
            {{ $sale->customer_name ?: 'Pelanggan umum' }}
        </h3>

        <p class="mb-0 opacity-75">
            {{ $when->locale('id')->translatedFormat('l, d F Y') }},
            {{ $when->format('H:i') }} WIB
        </p>
    </div>

    <div class="health-pill">
        <div class="health-num" style="font-size:1.5rem">
            Rp {{ number_format($sale->total, 0, ',', '.') }}
        </div>

        <div class="small">
            @if ($isCompleted)
                Selesai
            @elseif ($isPending)
                Menunggu
            @else
                Dibatalkan
            @endif
        </div>
    </div>
</div>

{{-- Informasi status dan pembayaran --}}
@if ($isCancelled)
    <div class="alert alert-secondary rounded-4">
        <i class="bi bi-info-circle"></i>
        Transaksi ini sudah dibatalkan.
    </div>
@elseif ($isPending)
    <div class="alert alert-warning rounded-4">
        <i class="bi bi-hourglass-split"></i>

        @if ($isTransfer)
            Menunggu pembayaran transfer. Konfirmasi setelah uang masuk
            ke rekening. Stok belum berkurang.
        @else
            Menunggu pelanggan datang dan membayar tunai di toko.
            Konfirmasi setelah pembayaran diterima. Stok belum berkurang.
        @endif
    </div>
@endif

<div class="mc-card reveal" style="animation-delay:.1s">

    {{-- Informasi transaksi --}}
    <div class="info-grid mb-4">
        <div class="info-item">
            <div class="lbl">Pelanggan</div>

            <div class="val" style="font-size:1.05rem">
                {{ $sale->customer_name ?: 'Pelanggan umum' }}
            </div>

            <div class="small text-muted">
                {{ $sale->customer ? 'Pelanggan terdaftar' : 'Tidak terdaftar' }}
            </div>
        </div>

        <div class="info-item">
            <div class="lbl">Kasir</div>

            <div class="val" style="font-size:1.05rem">
                {{ $sale->user ? $sale->user->name : '-' }}
            </div>

            <div class="small text-muted">
                {{ $sale->user ? $sale->user->roleLabel() : 'Belum dikonfirmasi' }}
            </div>
        </div>

        <div class="info-item">
            <div class="lbl">Pembayaran</div>

            <div class="val" style="font-size:1.05rem">
                {{ $isTransfer ? 'Transfer' : 'Tunai' }}
            </div>
        </div>

        <div class="info-item">
            <div class="lbl">Status</div>
            <div class="val">@include('sales._status')</div>
        </div>
    </div>

    {{-- Rincian produk --}}
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Harga satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($sale->items as $item)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ $item->product?->name ?? 'Produk tidak tersedia' }}
                            </div>

                            @if ($item->product?->sku)
                                <span class="sku-chip">
                                    {{ $item->product->sku }}
                                </span>
                            @endif
                        </td>

                        <td class="text-end">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-end">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>

                        <td class="text-end fw-bold">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada rincian produk untuk transaksi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">
                        Total
                    </td>

                    <td class="text-end fw-bold text-mc"
                        style="font-size:1.15rem">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Tombol tindakan --}}
    <div class="d-flex flex-wrap gap-2 mt-4">

        <a href="{{ route('sales.index') }}"
           class="btn btn-soft d-inline-flex align-items-center text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>

        @if ($isCompleted)
            <a href="{{ route('sales.receipt', $sale) }}"
               target="_blank"
               class="btn btn-mc">
                <i class="bi bi-printer"></i>
                Cetak struk
            </a>
        @endif

        @if ($isPending)
            <form method="POST"
                  action="{{ route('sales.confirm', $sale) }}"
                  onsubmit="return confirm('Konfirmasi pesanan ini? Pastikan pembayaran sudah diterima. Stok produk akan dikurangi.')">
                @csrf

                <button type="submit" class="btn btn-mc">
                    <i class="bi bi-check2-circle"></i>
                    Konfirmasi pesanan
                </button>
            </form>

            <form method="POST"
                  action="{{ route('sales.reject', $sale) }}"
                  onsubmit="return confirm('Tolak pesanan ini?')">
                @csrf

                <button type="submit" class="btn-logout">
                    <i class="bi bi-x-circle"></i>
                    Tolak pesanan
                </button>
            </form>
        @endif

        {{-- Pembatalan transaksi selesai hanya untuk Admin --}}
        @if (auth()->user()->isAdmin() && $isCompleted)
            @if ($sale->sold_at && $sale->sold_at->gt(now()->subDay()))
                <form method="POST"
                      action="{{ route('sales.cancel', $sale) }}"
                      onsubmit="return confirm('Batalkan transaksi ini sebagai koreksi kesalahan input? Stok produk akan dikembalikan.')">
                    @csrf

                    <button type="submit" class="btn-logout">
                        <i class="bi bi-x-circle"></i>
                        Batalkan (koreksi kesalahan)
                    </button>
                </form>
            @else
                <span class="small text-muted align-self-center">
                    Transaksi selesai lebih dari 24 jam tidak dapat dibatalkan.
                </span>
            @endif
        @endif

    </div>
</div>

@endsection