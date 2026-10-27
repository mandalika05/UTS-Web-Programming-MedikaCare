@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_icon', 'bi-grid-1x2')
@section('page_sub', 'Ringkasan apotek MedikaCare')

@section('content')
    <div class="hero-banner mb-4 reveal">
        <div>
            <div class="small opacity-75 mb-1">{{ $today }}</div>
            <h3 class="fw-bold mb-1">{{ $greeting }}, {{ auth()->user()->name }}</h3>
            <p class="mb-0 opacity-75">
                {{ $isAdmin ? 'Berikut ringkasan penjualan dan stok seluruh apotek.' : 'Berikut ringkasan penjualan Anda dan stok apotek saat ini.' }}
            </p>
        </div>
        <div class="health-pill">
            <div class="health-num"><span data-count="{{ $stockHealth }}">0</span>%</div>
            <div class="small">stok aman</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.05s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-num" style="font-size:1.5rem" data-money data-count="{{ (int) $salesToday }}">Rp 0</div>
                    <div class="text-muted">Penjualan hari ini</div>
                    <div class="small text-muted">{{ $transactionsToday }} transaksi</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.1s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="stat-num" style="font-size:1.5rem" data-money data-count="{{ (int) $salesMonth }}">Rp 0</div>
                    <div class="text-muted">Penjualan bulan ini</div>
                    <div class="small text-muted">{{ $transactionsMonth }} transaksi</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.15s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-num" data-count="{{ $transactionsMonth }}">0</div>
                    <div class="text-muted">Transaksi bulan ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.2s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-capsule"></i></div>
                <div>
                    <div class="stat-num" data-count="{{ $totalProducts }}">0</div>
                    <div class="text-muted">Total produk</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.25s">
            <div class="mc-card stat-card">
                <div class="stat-ico warn"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-num" data-count="{{ $lowStock }}">0</div>
                    <div class="text-muted">Stok rendah</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 reveal" style="animation-delay:.3s">
            <div class="mc-card stat-card">
                <div class="stat-ico"><i class="bi bi-tags"></i></div>
                <div>
                    <div class="stat-num" data-count="{{ $totalCategories }}">0</div>
                    <div class="text-muted">Kategori</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-7 reveal" style="animation-delay:.35s">
            <div class="mc-card h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-mc"></i> Penjualan 7 hari terakhir</h5>
                <div class="chart-box">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5 reveal" style="animation-delay:.4s">
            <div class="mc-card h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart text-mc"></i> Stok per kategori</h5>
                <div class="chart-box">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="mc-card mt-3 reveal" style="animation-delay:.45s">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-exclamation-triangle text-warning"></i> Perlu segera diisi ulang
        </h5>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok</th>
                        <th>Minimum</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lowStockItems as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->name }}</div>
                                <div class="small text-muted">{{ $item->category->name }}</div>
                            </td>
                            <td>{{ $item->stock }}</td>
                            <td>{{ $item->minimum_stock }}</td>
                            <td>
                                @if ($item->stock == 0)
                                    <span class="badge-s b-out">Habis</span>
                                @else
                                    <span class="badge-s b-low">Hampir habis</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Semua stok aman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // angka menghitung naik
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseInt(el.dataset.count, 10) || 0;
        var money = el.hasAttribute('data-money');
        var start = null;
        function step(ts) {
            if (start === null) start = ts;
            var p = Math.min((ts - start) / 900, 1);
            var value = Math.round(target * (1 - Math.pow(1 - p, 3)));
            el.textContent = money ? 'Rp ' + value.toLocaleString('id-ID') : value;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    // grafik penjualan 7 hari
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($salesLabels),
            datasets: [{
                data: @json($salesValues),
                borderColor: '#12A08A',
                backgroundColor: 'rgba(18, 160, 138, .15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#0B6E5F'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#EAF3F0' },
                    ticks: {
                        callback: function (v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // grafik stok per kategori
    new Chart(document.getElementById('stockChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                data: @json($chartValues),
                backgroundColor: ['#0B6E5F', '#12A08A', '#7AB648', '#8FD3C1'],
                borderRadius: 10,
                maxBarThickness: 46
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#EAF3F0' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush