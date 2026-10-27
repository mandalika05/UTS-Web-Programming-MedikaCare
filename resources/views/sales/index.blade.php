@extends('layouts.app')
@section('title', 'Penjualan')
@section('page_title', 'Penjualan')
@section('page_icon', 'bi-cart-check')
@section('page_sub', 'Kasir dan riwayat transaksi')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4 reveal">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-num" style="font-size:1.45rem">Rp {{ number_format($todaySum, 0, ',', '.') }}</div>
                    <div class="text-muted">Penjualan hari ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal" style="animation-delay:.08s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-num">{{ $todayCount }}</div>
                    <div class="text-muted">Transaksi hari ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal" style="animation-delay:.16s">
            <div class="mc-card stat-card">
                <div class="stat-ico warn"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-num">{{ $countPending }}</div>
                    <div class="text-muted">Menunggu konfirmasi</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mc-card reveal" style="animation-delay:.2s">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            @php($base = array_filter(['q' => request('q'), 'from' => request('from'), 'to' => request('to')]))
            <div class="pill-tabs">
                <a href="{{ route('sales.index', $base) }}"
                   class="pill-tab {{ request('status') ? '' : 'active' }}">
                    Semua <span class="cnt">{{ $countAll }}</span>
                </a>
                <a href="{{ route('sales.index', $base + ['status' => 'pending']) }}"
                   class="pill-tab {{ request('status') === 'pending' ? 'active' : '' }}">
                    Menunggu <span class="cnt">{{ $countPending }}</span>
                </a>
                <a href="{{ route('sales.index', $base + ['status' => 'completed']) }}"
                   class="pill-tab {{ request('status') === 'completed' ? 'active' : '' }}">
                    Selesai <span class="cnt">{{ $countDone }}</span>
                </a>
                <a href="{{ route('sales.index', $base + ['status' => 'cancelled']) }}"
                   class="pill-tab {{ request('status') === 'cancelled' ? 'active' : '' }}">
                    Dibatalkan <span class="cnt">{{ $countCancelled }}</span>
                </a>
            </div>

            <a href="{{ route('sales.create') }}" class="btn btn-mc">
                <i class="bi bi-plus-lg"></i> Penjualan Baru
            </a>
        </div>

        <form method="GET" action="{{ route('sales.index') }}" class="row g-2 mb-4">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Cari nomor atau pelanggan...">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Dari tanggal">
            </div>
            <div class="col-6 col-lg-2">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Sampai tanggal">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button class="btn btn-mc flex-fill">Terapkan</button>
                <a href="{{ route('sales.index') }}"
                   class="btn btn-soft d-inline-flex align-items-center text-decoration-none">Reset</a>
            </div>
            @if ($errors->any())
                <div class="col-12 text-danger small ms-2">{{ $errors->first() }}</div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Bayar</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        @php($when = $sale->sold_at ?? $sale->created_at)
                        <tr>
                            <td><span class="sku-chip">{{ $sale->sale_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $when->locale('id')->translatedFormat('d M Y') }}</div>
                                <div class="small text-muted">{{ $when->format('H:i') }} WIB</div>
                            </td>
                            <td class="fw-semibold">{{ $sale->customer_name ?: 'Pelanggan umum' }}</td>
                            <td>{{ $sale->user ? $sale->user->name : '-' }}</td>
                            <td>{{ $sale->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}</td>
                            <td class="fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td>@include('sales._status')</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('sales.show', $sale) }}" class="btn-soft text-decoration-none">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if ($sale->status === 'completed')
                                    <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                                       class="btn-soft text-decoration-none">
                                        <i class="bi bi-printer"></i> Struk
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-box">
                                    <i class="bi bi-cart-check"></i>
                                    Belum ada transaksi yang cocok.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $sales->links() }}</div>
    </div>
@endsection