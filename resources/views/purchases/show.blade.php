@extends('layouts.app')
@section('title', $purchase->purchase_number)
@section('page_title', 'Detail Pembelian')
@section('page_icon', 'bi-receipt')
@section('page_sub', $purchase->purchase_number)

@section('content')
    <div class="hero-banner mb-4 reveal">
        <div>
            <span class="sku-chip mb-2">{{ $purchase->purchase_number }}</span>
            <h3 class="fw-bold mb-1">{{ $purchase->supplier->name }}</h3>
            <p class="mb-0 opacity-75">
                {{ $purchase->purchased_at->locale('id')->translatedFormat('l, d F Y') }},
                {{ $purchase->purchased_at->format('H:i') }} WIB
            </p>
        </div>
        <div class="health-pill">
            <div class="health-num" style="font-size:1.5rem">
                Rp {{ number_format($purchase->total, 0, ',', '.') }}
            </div>
            <div class="small">{{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</div>
        </div>
    </div>

    @if ($purchase->status === 'cancelled')
        <div class="alert alert-secondary rounded-4">
            <i class="bi bi-info-circle"></i>
            Pembelian ini sudah dibatalkan, stok produk sudah dikurangi kembali.
        </div>
    @endif

    <div class="mc-card reveal" style="animation-delay:.1s">
        <div class="info-grid mb-4">
            <div class="info-item">
                <div class="lbl">Supplier</div>
                <div class="val" style="font-size:1.05rem">{{ $purchase->supplier->name }}</div>
                <div class="small text-muted">{{ $purchase->supplier->phone ?: '-' }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Dicatat oleh</div>
                <div class="val" style="font-size:1.05rem">{{ $purchase->user->name }}</div>
                <div class="small text-muted">{{ $purchase->user->roleLabel() }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Status</div>
                <div class="val">
                    @if ($purchase->status === 'completed')
                        <span class="badge-s b-ok">Selesai</span>
                    @else
                        <span class="badge-s b-out">Dibatalkan</span>
                    @endif
                </div>
            </div>
        </div>

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
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->product->name }}</div>
                                <span class="sku-chip">{{ $item->product->sku }}</span>
                            </td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold text-mc" style="font-size:1.15rem">
                            Rp {{ number_format($purchase->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('purchases.index') }}"
               class="btn btn-soft d-inline-flex align-items-center text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            @if (auth()->user()->isAdmin() && $purchase->status === 'completed')
                <form method="POST" action="{{ route('purchases.cancel', $purchase) }}"
                      onsubmit="return confirm('Batalkan pembelian ini? Stok produk akan dikurangi kembali.')">
                    @csrf
                    <button class="btn-logout">
                        <i class="bi bi-x-circle"></i> Batalkan pembelian
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
