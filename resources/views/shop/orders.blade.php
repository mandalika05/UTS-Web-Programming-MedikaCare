@extends('layouts.shop')

@section('title', 'Pesanan Saya')

@section('content')
<div class="hero-banner mb-4 reveal">
    <div>
        <span class="sku-chip mb-2">MedikaCare</span>
        <h3 class="fw-bold mb-1">Pesanan Saya</h3>
        <p class="mb-0 opacity-75">
            Pantau status dan detail pesanan obatmu.
        </p>
    </div>
</div>

<div class="mc-card reveal">
    @if ($orders->isEmpty())
        <div class="empty-box">
            <i class="bi bi-bag"></i>
            Kamu belum memiliki pesanan.
            <div class="mt-3">
                <a href="{{ route('shop.index') }}" class="btn btn-mc">
                    Mulai Belanja
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $sale)
                        <tr>
                            <td class="fw-semibold">
                                {{ $sale->sale_number }}
                            </td>
                            <td>
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if ($sale->status === 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @elseif ($sale->status === 'pending')
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @else
                                    <span class="badge bg-secondary">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <a
                                    href="{{ route('orders.show', $sale) }}"
                                    class="btn btn-soft"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
