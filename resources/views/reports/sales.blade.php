@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page_title', 'Laporan Penjualan')
@section('page_icon', 'bi-bar-chart-line')
@section('page_sub', 'Harian, mingguan, bulanan, dan per kasir')

@section('content')
    @php($keep = array_filter(['date' => $date, 'staff_id' => $staffId]))

    <div class="mc-card reveal mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div class="pill-tabs">
                <a href="{{ route('reports.sales', ['period' => 'harian'] + $keep) }}"
                   class="pill-tab {{ $period === 'harian' ? 'active' : '' }}">Harian</a>
                <a href="{{ route('reports.sales', ['period' => 'mingguan'] + $keep) }}"
                   class="pill-tab {{ $period === 'mingguan' ? 'active' : '' }}">Mingguan</a>
                <a href="{{ route('reports.sales', ['period' => 'bulanan'] + $keep) }}"
                   class="pill-tab {{ $period === 'bulanan' ? 'active' : '' }}">Bulanan</a>
            </div>

            <a href="{{ route('reports.print', ['period' => $period] + $keep) }}"
               target="_blank" class="btn btn-mc">
                <i class="bi bi-printer"></i> Cetak laporan
            </a>
        </div>

        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2">
            <input type="hidden" name="period" value="{{ $period }}">

            <div class="col-md-4">
                <label class="form-label">
                    {{ $period === 'harian' ? 'Tanggal' : ($period === 'mingguan' ? 'Tanggal dalam minggu' : 'Tanggal dalam bulan') }}
                </label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>

            <div class="col-md-5">
                <label class="form-label">Kasir</label>
                <select name="staff_id" class="form-select">
                    <option value="">Semua kasir</option>
                    @foreach ($staffOptions as $staff)
                        <option value="{{ $staff->id }}" @selected((string) $staffId === (string) $staff->id)>
                            {{ $staff->name }} — {{ ucfirst($staff->role) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-mc flex-grow-1">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
                <a href="{{ route('reports.sales') }}"
                   class="btn btn-soft d-inline-flex align-items-center text-decoration-none">Reset</a>
            </div>

            @if ($errors->any())
                <div class="col-12 text-danger small">{{ $errors->first() }}</div>
            @endif
        </form>
    </div>

    <div class="hero-banner mb-3 reveal" style="animation-delay:.05s">
        <div>
            <div class="small opacity-75 mb-1">Periode laporan &middot; {{ $staffLabel }}</div>
            <h3 class="fw-bold mb-0">{{ $label }}</h3>
        </div>
        <div class="health-pill">
            <div class="health-num" style="font-size:1.6rem">
                Rp {{ number_format($total, 0, ',', '.') }}
            </div>
            <div class="small">total penjualan</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4 reveal" style="animation-delay:.1s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-num">{{ $count }}</div>
                    <div class="text-muted">Jumlah transaksi</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 reveal" style="animation-delay:.15s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-calculator"></i></div>
                <div>
                    <div class="stat-num" style="font-size:1.45rem">
                        Rp {{ number_format($average, 0, ',', '.') }}
                    </div>
                    <div class="text-muted">Rata-rata per transaksi</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 reveal" style="animation-delay:.2s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-num">{{ $units }}</div>
                    <div class="text-muted">Unit terjual</div>
                </div>
            </div>
        </div>
    </div>

    @if (count($chartLabels) > 0)
        <div class="mc-card mt-3 reveal" style="animation-delay:.25s">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-graph-up text-mc"></i> Penjualan per hari
            </h5>
            <div class="chart-box">
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-lg-5 reveal" style="animation-delay:.3s">
            <div class="mc-card h-100">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-person-badge text-mc"></i> Penjualan per kasir
                </h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kasir</th>
                                <th class="text-end">Transaksi</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perStaff as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $names[$row->user_id] ?? '-' }}</td>
                                    <td class="text-end">{{ $row->trx }}</td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($row->revenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Belum ada penjualan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7 reveal" style="animation-delay:.35s">
            <div class="mc-card h-100">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-trophy text-mc"></i> Produk terlaris
                </h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Produk</th>
                                <th class="text-end">Terjual</th>
                                <th class="text-end">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $product)
                                <tr>
                                    <td>
                                        <span class="rank-no">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </td>
                                    <td class="text-end">{{ $product->qty }}</td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($product->revenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada penjualan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (count($chartLabels) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const reportCanvas = document.getElementById('reportChart');

            if (reportCanvas) {
                new Chart(reportCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Penjualan (Rp)',
                            data: @json($chartValues),
                            backgroundColor: 'rgba(11, 110, 95, 0.75)',
                            borderColor: '#0B6E5F',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endif
@endpush