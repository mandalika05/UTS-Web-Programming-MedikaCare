@extends('layouts.app')
@section('title', 'Pembelian')
@section('page_title', 'Pembelian')
@section('page_icon', 'bi-box-arrow-in-down')
@section('page_sub', 'Stok masuk dari supplier')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-4 reveal">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-num">{{ $monthCount }}</div>
                    <div class="text-muted">Pembelian bulan ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal" style="animation-delay:.08s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-num" style="font-size:1.45rem">Rp {{ number_format($monthSum, 0, ',', '.') }}</div>
                    <div class="text-muted">Total belanja bulan ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal" style="animation-delay:.16s">
            <div class="mc-card stat-card">
                <div class="stat-ico warn"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-num">{{ $countCancelled }}</div>
                    <div class="text-muted">Dibatalkan</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mc-card reveal" style="animation-delay:.2s">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            @php($base = array_filter(['q' => request('q'), 'from' => request('from'), 'to' => request('to')]))
            <div class="pill-tabs">
                <a href="{{ route('purchases.index', $base) }}"
                   class="pill-tab {{ request('status') ? '' : 'active' }}">
                    Semua <span class="cnt">{{ $countAll }}</span>
                </a>
                <a href="{{ route('purchases.index', $base + ['status' => 'completed']) }}"
                   class="pill-tab {{ request('status') === 'completed' ? 'active' : '' }}">
                    Selesai <span class="cnt">{{ $countDone }}</span>
                </a>
                <a href="{{ route('purchases.index', $base + ['status' => 'cancelled']) }}"
                   class="pill-tab {{ request('status') === 'cancelled' ? 'active' : '' }}">
                    Dibatalkan <span class="cnt">{{ $countCancelled }}</span>
                </a>
            </div>

            <a href="{{ route('purchases.create') }}" class="btn btn-mc">
                <i class="bi bi-plus-lg"></i> Pembelian Baru
            </a>
        </div>

        <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 mb-4">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Cari nomor atau supplier...">
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
                <a href="{{ route('purchases.index') }}"
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
                        <th>Supplier</th>
                        <th>Dicatat oleh</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td><span class="sku-chip">{{ $purchase->purchase_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->purchased_at->locale('id')->translatedFormat('d M Y') }}</div>
                                <div class="small text-muted">{{ $purchase->purchased_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="fw-semibold">{{ $purchase->supplier->name }}</td>
                            <td>{{ $purchase->user->name }}</td>
                            <td class="fw-bold">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td>
                                @if ($purchase->status === 'completed')
                                    <span class="badge-s b-ok">Selesai</span>
                                @else
                                    <span class="badge-s b-out">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   class="btn-soft text-decoration-none">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-box">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                    Belum ada pembelian yang cocok.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $purchases->links() }}</div>
    </div>
@endsection
