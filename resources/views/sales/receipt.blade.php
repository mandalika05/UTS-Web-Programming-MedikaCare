<!DOCTYPE html>

<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

<title>Struk {{ $sale->sale_number }} - MedikaCare</title>

<link rel="icon" type="image/png" href="{{ asset('images/logo-transparan.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1F2D2B;
        background: #E4F1ED;
    }

    .toolbar {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: .6rem;
        padding: 1.25rem 1rem 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: .65rem 1.3rem;
        border-radius: 999px;
        border: 1px solid #0B6E5F;
        background: #0B6E5F;
        color: #fff;
        font: 700 .9rem 'Plus Jakarta Sans', sans-serif;
        text-decoration: none;
        cursor: pointer;
    }

    .btn.light {
        background: #fff;
        color: #0B6E5F;
    }

    .receipt {
        width: 340px;
        max-width: 94%;
        margin: 1.25rem auto 2rem;
        padding: 1.5rem 1.4rem;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(11, 110, 95, .15);
        font-size: .85rem;
    }

    .center {
        text-align: center;
    }

    .logo {
        height: 78px;
        max-width: 100%;
        object-fit: contain;
    }

    .muted {
        color: #6b7f7b;
    }

    .dash {
        border-top: 1px dashed #9db5b0;
        margin: .85rem 0;
    }

    .row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
        margin-bottom: .3rem;
    }

    .row > :last-child {
        text-align: right;
        overflow-wrap: anywhere;
    }

    .item {
        margin-bottom: .7rem;
    }

    .item .name {
        font-weight: 700;
        overflow-wrap: anywhere;
        margin-bottom: .2rem;
    }

    .total {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0B6E5F;
    }

    .notice {
        margin-top: .9rem;
        padding-top: .75rem;
        border-top: 1px dashed #9db5b0;
        font-size: .72rem;
        line-height: 1.6;
    }

    .footer {
        line-height: 1.7;
    }

    @page {
        margin: 6mm;
    }

    @media print {
        body {
            background: #fff;
        }

        .no-print {
            display: none !important;
        }

        .receipt {
            box-shadow: none;
            margin: 0 auto;
            border-radius: 0;
            width: 100%;
            max-width: 100%;
            padding: 0;
        }
    }
</style>

</head>

<body>
    <div class="toolbar no-print">
        <button class="btn" type="button" onclick="window.print()">
            <i class="bi bi-printer"></i>
            Cetak struk
        </button>

    <a class="btn light" href="{{ $backUrl ?? route('sales.show', $sale) }}">
        <i class="bi bi-arrow-left"></i>
        Kembali
    </a>
</div>

<div class="receipt">
    <div class="center">
        <img
            class="logo"
            src="{{ asset('images/logo-transparan.png') }}"
            alt="Apotek MedikaCare"
        >

        <div class="muted" style="margin-top: .3rem;">
            Jl. Kesehatan No. 10
        </div>

        <div class="muted">
            Telp. 0812-0000-0000
        </div>
    </div>

    <div class="dash"></div>

    <div class="row">
        <span class="muted">No. Transaksi</span>
        <strong>{{ $sale->sale_number }}</strong>
    </div>

    <div class="row">
        <span class="muted">Tanggal</span>
        <span>
            {{ $sale->sold_at
                ? $sale->sold_at->format('d/m/Y H:i')
                : $sale->created_at?->format('d/m/Y H:i') ?? '-' }}
        </span>
    </div>

    <div class="row">
        <span class="muted">Kasir</span>
        <span>{{ $sale->user?->name ?? '-' }}</span>
    </div>

    <div class="row">
        <span class="muted">Pelanggan</span>
        <span>{{ $sale->customer_name ?: 'Umum' }}</span>
    </div>

    <div class="dash"></div>

    @forelse ($sale->items as $item)
        <div class="item">
            <div class="name">
                {{ $item->product?->name ?? 'Produk tidak tersedia' }}
            </div>

            <div class="row">
                <span class="muted">
                    {{ $item->quantity }}
                    x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                </span>

                <span>
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                </span>
            </div>
        </div>
    @empty
        <div class="center muted">
            Tidak ada detail produk pada transaksi ini.
        </div>
    @endforelse

    <div class="dash"></div>

    <div class="row total">
        <span>TOTAL</span>
        <span>
            Rp {{ number_format($sale->total, 0, ',', '.') }}
        </span>
    </div>

    <div class="row" style="margin-top: .5rem;">
        <span class="muted">Pembayaran</span>

        <span>
            {{ $sale->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}
        </span>
    </div>

    <div class="row">
        <span class="muted">Status</span>

        <span>
            @switch($sale->status)
                @case('completed')
                    Selesai
                    @break

                @case('pending')
                    Menunggu konfirmasi
                    @break

                @case('cancelled')
                    Dibatalkan
                    @break

                @default
                    {{ ucfirst($sale->status ?? 'Tidak diketahui') }}
            @endswitch
        </span>
    </div>

    <div class="notice center muted">
        <strong>Informasi</strong><br>
        Harap periksa kembali barang dan jumlah pembelian sebelum meninggalkan apotek.
        Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar.
    </div>

    <div class="dash"></div>

    <div class="center muted footer">
        Terima kasih telah berbelanja di MedikaCare.<br>
        Semoga lekas sehat.<br>
        <strong>Sehat Bersama, Lebih Baik</strong>
    </div>
</div>

</body>
</html>