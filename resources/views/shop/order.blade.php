@extends('layouts.shop')

@section('title', 'Detail Pesanan')

@section('content')
@php
$statusMap = [
'pending' => [
'label' => 'Menunggu',
'class' => 'od-badge--wait',
'step' => 1,
],
'completed' => [
'label' => 'Selesai',
'class' => 'od-badge--done',
'step' => 3,
],
'cancelled' => [
'label' => 'Dibatalkan',
'class' => 'od-badge--cancel',
'step' => 0,
],
];

$status = $statusMap[$sale->status] ?? [
    'label' => ucfirst($sale->status),
    'class' => 'od-badge--wait',
    'step' => 1,
];

$items = $sale->items;
$itemCount = $items->sum('quantity');
$productCount = $items->count();

$isTransfer = $sale->payment_method === 'transfer';
$isCompleted = $sale->status === 'completed';
$isCancelled = $sale->status === 'cancelled';

@endphp

<div class="od-page">

{{-- Header --}}
<header class="od-head">
    <div>
        <span class="od-eyebrow">MEDIKACARE · PESANAN</span>
        <h1 class="od-title">Detail Pesanan</h1>
        <p class="od-sub">
            Nomor pesanan:
            <strong>{{ $sale->sale_number }}</strong>
        </p>
    </div>

    <a href="{{ route('orders.index') }}" class="od-btn od-btn--ghost">
        <span aria-hidden="true">←</span>
        Kembali
    </a>
</header>

{{-- Banner ringkasan --}}
<section class="od-card od-hero">
    <div>
        <span class="od-hero-label">Total pembayaran</span>
        <div class="od-hero-total">
            Rp {{ number_format($sale->total, 0, ',', '.') }}
        </div>
        <p class="od-hero-date">
            Dipesan pada
            {{ $sale->created_at->format('d M Y, H:i') }}
        </p>
    </div>

    <div class="od-hero-status">
        <span class="od-status-caption">Status pesanan</span>
        <span class="od-badge {{ $status['class'] }}">
            {{ $status['label'] }}
        </span>
    </div>
</section>

{{-- Kartu ringkasan --}}
<div class="od-grid od-summary-grid">

    <section class="od-card od-summary-card">
        <span class="od-summary-label">Nomor Pesanan</span>
        <strong class="od-summary-value">{{ $sale->sale_number }}</strong>
        <span class="od-summary-hint">Nomor transaksi kamu</span>
    </section>

    <section class="od-card od-summary-card">
        <span class="od-summary-label">Pembayaran</span>
        <strong class="od-summary-value">
            {{ $isTransfer ? 'Transfer' : 'Tunai' }}
        </strong>
        <span class="od-summary-hint">
            {{ $isTransfer ? 'Melalui transfer' : 'Bayar di apotek' }}
        </span>
    </section>

    <section class="od-card od-summary-card">
        <span class="od-summary-label">Jumlah Item</span>
        <strong class="od-summary-value">{{ $itemCount }} barang</strong>
        <span class="od-summary-hint">{{ $productCount }} jenis produk</span>
    </section>

    <section class="od-card od-summary-card">
        <span class="od-summary-label">Status</span>
        <div class="od-summary-status">
            <span class="od-badge {{ $status['class'] }}">
                {{ $status['label'] }}
            </span>
        </div>
        <span class="od-summary-hint">Status pesanan saat ini</span>
    </section>

</div>

{{-- Perjalanan pesanan --}}
<section class="od-card">
    <div class="od-section-heading">
        <span class="od-section-icon" aria-hidden="true">✓</span>
        <div>
            <h2 class="od-card-title">Perjalanan Pesanan</h2>
            <p class="od-muted">Pantau status pesananmu di sini.</p>
        </div>
    </div>

    @if ($isCancelled)
        <div class="od-notice od-notice--cancel">
            Pesanan ini telah dibatalkan.
        </div>
    @else
        <ol class="od-steps">
            <li class="od-step is-done">
                <span class="od-step-dot">✓</span>
                <span class="od-step-label">Pesanan dibuat</span>
            </li>

            <li class="od-step {{ $isCompleted ? 'is-done' : 'is-active' }}">
                <span class="od-step-dot">
                    {{ $isCompleted ? '✓' : '2' }}
                </span>
                <span class="od-step-label">
                    @if ($isCompleted)
                        Dikonfirmasi apotek
                    @elseif ($isTransfer)
                        Menunggu pembayaran transfer
                    @else
                        Menunggu bayar di toko
                    @endif
                </span>
            </li>

            <li class="od-step {{ $isCompleted ? 'is-done' : '' }}">
                <span class="od-step-dot">
                    {{ $isCompleted ? '✓' : '3' }}
                </span>
                <span class="od-step-label">Selesai</span>
            </li>
        </ol>
    @endif

    @if ($isCompleted)
        <div class="od-notice od-notice--success">
            Pesanan telah selesai. Barang yang sudah dibeli tidak dapat
            dikembalikan atau ditukar.
        </div>
    @endif
</section>

{{-- Informasi pesanan dan pembayaran --}}
<div class="od-grid od-detail-grid">

    <section class="od-card">
        <div class="od-section-heading">
            <span class="od-section-icon" aria-hidden="true">▤</span>
            <div>
                <h2 class="od-card-title">Informasi Pesanan</h2>
                <p class="od-muted">Rincian transaksi kamu.</p>
            </div>
        </div>

        <dl class="od-info">
            <div>
                <dt>Nomor pesanan</dt>
                <dd>{{ $sale->sale_number }}</dd>
            </div>

            <div>
                <dt>Tanggal pesanan</dt>
                <dd>{{ $sale->created_at->format('d-m-Y H:i') }}</dd>
            </div>

            <div>
                <dt>Status</dt>
                <dd class="od-info-status">
                    <span class="od-badge {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </dd>
            </div>

            <div>
                <dt>Metode pembayaran</dt>
                <dd>{{ $isTransfer ? 'Transfer' : 'Tunai' }}</dd>
            </div>
        </dl>
    </section>

    <section class="od-card od-payment-card">
        <div class="od-section-heading">
            <span class="od-section-icon" aria-hidden="true">♡</span>
            <div>
                <h2 class="od-card-title">Informasi Pembayaran</h2>
                <p class="od-muted">Petunjuk pembayaran pesanan.</p>
            </div>
        </div>

        @if ($isCancelled)
            <div class="od-notice od-notice--cancel">
                Pesanan dibatalkan. Silakan hubungi apotek jika membutuhkan bantuan.
            </div>

        @elseif ($isCompleted)
            <div class="od-notice od-notice--success">
                Pesanan telah selesai. Terima kasih telah berbelanja di MedikaCare!
            </div>

        @elseif ($isTransfer)
            <div class="od-payment-highlight">
                Pembayaran melalui transfer
            </div>

            <p class="od-payment-text">
                Silakan transfer sesuai nominal pesanan ke salah satu rekening
                demo di bawah ini.
            </p>

            {{-- Rekening demo A --}}
            <div style="margin: 1rem 0; padding: 1rem; border: 1px solid #d6e5e0; border-radius: 12px;">
                <div style="font-weight: 700; margin-bottom: .5rem;">
                    Bank Contoh A
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;">
                    <strong>0000 1234 5678</strong>
                    <button
                        type="button"
                        class="od-btn od-btn--ghost"
                        onclick="salinTeks('000012345678', this)"
                    >Salin</button>
                </div>
                <div class="od-muted" style="margin-top: .4rem;">
                    Rekening demo — bukan rekening bank sungguhan.
                </div>
            </div>

            {{-- Rekening demo B --}}
            <div style="margin: 1rem 0; padding: 1rem; border: 1px solid #d6e5e0; border-radius: 12px;">
                <div style="font-weight: 700; margin-bottom: .5rem;">
                    Bank Contoh B
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;">
                    <strong>0000 8765 4321</strong>
                    <button
                        type="button"
                        class="od-btn od-btn--ghost"
                        onclick="salinTeks('000087654321', this)"
                    >Salin</button>
                </div>
                <div class="od-muted" style="margin-top: .4rem;">
                    Rekening demo — bukan rekening bank sungguhan.
                </div>
            </div>

            {{-- Nominal transfer --}}
            <div style="margin: 1rem 0; padding: 1rem; background: #e4f1ed; border-radius: 12px;">
                <div class="od-muted" style="margin-bottom: .35rem;">
                    Nominal transfer
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;">
                    <strong style="font-size: 1.15rem; color: #0b6e5f;">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </strong>
                    <button
                        type="button"
                        class="od-btn od-btn--ghost"
                        onclick="salinTeks('{{ (int) $sale->total }}', this)"
                    >Salin</button>
                </div>
            </div>

            {{-- Berita transfer --}}
            <div style="margin: 1rem 0; padding: 1rem; border: 1px solid #d6e5e0; border-radius: 12px;">
                <div style="font-weight: 700; margin-bottom: .35rem;">
                    Berita transfer
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;">
                    <strong>{{ $sale->sale_number }}</strong>
                    <button
                        type="button"
                        class="od-btn od-btn--ghost"
                        onclick="salinTeks(@js($sale->sale_number), this)"
                    >Salin</button>
                </div>
                <p class="od-muted" style="margin: .5rem 0 0;">
                    Cantumkan nomor pesanan ini pada berita transfer.
                </p>
            </div>

            <p class="od-payment-footnote">
                Setelah pembayaran masuk, staf apotek akan memeriksa mutasi
                rekening dan mengonfirmasi pesanan secara manual.
                Sistem ini belum menggunakan verifikasi pembayaran otomatis.
            </p>

        @else
            <div class="od-payment-highlight">
                Menunggu kamu datang dan bayar di toko
            </div>

            <p class="od-payment-text">
                Pesanan tunai baru dikonfirmasi setelah pembayaran diterima
                oleh petugas apotek.
            </p>

            <ol class="od-payment-text">
                <li>Datang ke Apotek MedikaCare.</li>
                <li>
                    Sebutkan nomor pesanan
                    <strong>{{ $sale->sale_number }}</strong>
                    kepada kasir.
                </li>
                <li>
                    Bayar sesuai total transaksi. Setelah pembayaran
                    diterima, staf akan mengonfirmasi pesanan.
                </li>
            </ol>

            <div class="od-cash-total">
                Rp {{ number_format($sale->total, 0, ',', '.') }}
            </div>
        @endif
    </section>

</div>

{{-- Rincian produk --}}
<section class="od-card">
    <div class="od-section-heading od-product-heading">
        <span class="od-section-icon" aria-hidden="true">▦</span>
        <div>
            <h2 class="od-card-title">Ringkasan Pesanan</h2>
            <p class="od-muted">
                Produk yang termasuk dalam transaksi ini.
            </p>
        </div>
    </div>

    <div class="od-table-wrap">
        <table class="od-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="od-right">Jumlah</th>
                    <th class="od-right">Harga satuan</th>
                    <th class="od-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <div class="od-product-name">
                                <span class="od-product-icon" aria-hidden="true">✚</span>
                                <span>
                                    {{ $item->product?->name ?? 'Produk tidak tersedia' }}
                                </span>
                            </div>
                        </td>

                        <td class="od-right">{{ $item->quantity }}</td>

                        <td class="od-right">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>

                        <td class="od-right od-subtotal">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="od-empty">
                            Belum ada rincian produk untuk pesanan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="od-total">
        <div>
            <span class="od-total-label">Total pembayaran</span>
            <span class="od-total-caption">
                Termasuk seluruh produk dalam pesanan
            </span>
        </div>

        <strong>
            Rp {{ number_format($sale->total, 0, ',', '.') }}
        </strong>
    </div>

    <div class="od-bottom-action">
        <a href="{{ route('orders.index') }}" class="od-btn od-btn--ghost">
            <span aria-hidden="true">←</span>
            Kembali ke Pesanan Saya
        </a>

        <a href="{{ url('/katalog') }}" class="od-btn od-btn--primary">
            Pesan Lagi
            <span aria-hidden="true">→</span>
        </a>
    </div>
</section>

</div>

<script>
    async function salinTeks(teks, tombol) {
        const teksAsli = tombol.textContent;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(teks);
            } else {
                const area = document.createElement('textarea');
                area.value = teks;
                area.style.position = 'fixed';
                area.style.opacity = '0';
                document.body.appendChild(area);
                area.select();

                const berhasil = document.execCommand('copy');
                area.remove();

                if (!berhasil) {
                    throw new Error('Penyalinan gagal.');
                }
            }

            tombol.textContent = 'Tersalin!';
            setTimeout(() => {
                tombol.textContent = teksAsli;
            }, 1500);
        } catch (error) {
            alert('Tidak dapat menyalin secara otomatis. Silakan salin teks secara manual: ' + teks);
        }
    }
</script>

@endsection