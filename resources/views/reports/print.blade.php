<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan - MedikaCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #eef2f5;
            color: #263238;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: #147d72;
            color: white;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn.light {
            background: white;
            color: #263238;
            border: 1px solid #d7dfe5;
        }

        .paper {
            max-width: 900px;
            min-height: 900px;
            margin: 0 auto;
            padding: 32px;
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .head {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 20px;
            border-bottom: 2px solid #147d72;
        }

        .head img {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .title h1 {
            margin: 0 0 8px;
            color: #147d72;
            font-size: 24px;
        }

        .title div {
            margin-bottom: 4px;
        }

        .muted {
            color: #718096;
        }

        .sum {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin: 22px 0;
        }

        .sum > div {
            padding: 14px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
        }

        .k {
            margin-bottom: 8px;
            color: #718096;
            font-size: 11px;
        }

        .v {
            color: #147d72;
            font-size: 17px;
            font-weight: 700;
        }

        h2 {
            margin: 24px 0 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e1e8ed;
            font-size: 14px;
        }

        table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 9px 8px;
            border: 1px solid #dfe5e9;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #edf7f5;
            color: #245b55;
            font-weight: 700;
        }

        .r {
            text-align: right;
        }

        tfoot td {
            background: #f4f8f9;
            font-weight: 700;
        }

        .foot {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #dfe5e9;
            line-height: 1.7;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        @media print {
            body {
                padding: 0;
                background: white;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            .paper {
                max-width: none;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .sum {
                gap: 8px;
            }

            .sum > div {
                padding: 9px;
            }

            .v {
                font-size: 13px;
            }

            th,
            td {
                padding: 6px;
            }

            h2 {
                margin-top: 18px;
            }

            tr {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button class="btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>

        <a class="btn light"
           href="{{ route('reports.sales', array_filter(['period' => $period, 'date' => $date, 'staff_id' => $staffId])) }}">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="paper">
        <div class="head">
            <img src="{{ asset('images/logo-transparan.png') }}" alt="Apotek MedikaCare">

            <div class="title">
                <h1>Laporan Penjualan</h1>
                <div>{{ $label }}</div>
                <div class="muted">{{ $staffLabel }}</div>
            </div>
        </div>

        <div class="sum">
            <div>
                <div class="k">Total penjualan</div>
                <div class="v">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>

            <div>
                <div class="k">Transaksi</div>
                <div class="v">{{ $count }}</div>
            </div>

            <div>
                <div class="k">Rata-rata</div>
                <div class="v">Rp {{ number_format($average, 0, ',', '.') }}</div>
            </div>

            <div>
                <div class="k">Unit terjual</div>
                <div class="v">{{ $units }}</div>
            </div>
        </div>

        <h2>Penjualan per kasir</h2>

        <table>
            <thead>
                <tr>
                    <th>Kasir</th>
                    <th class="r">Transaksi</th>
                    <th class="r">Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($perStaff as $row)
                    <tr>
                        <td>{{ $names[$row->user_id] ?? '-' }}</td>
                        <td class="r">{{ $row->trx }}</td>
                        <td class="r">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="muted">Belum ada penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Produk terlaris</h2>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th class="r">Terjual</th>
                    <th class="r">Pendapatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($topProducts as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ $product->name }}
                            <span class="muted">({{ $product->sku }})</span>
                        </td>
                        <td class="r">{{ $product->qty }}</td>
                        <td class="r">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">Belum ada penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Daftar transaksi</h2>

        <table>
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Waktu</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th>Bayar</th>
                    <th class="r">Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($transactions as $sale)
                    <tr>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer_name ?: 'Umum' }}</td>
                        <td>{{ $sale->user ? $sale->user->name : '-' }}</td>
                        <td>{{ $sale->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}</td>
                        <td class="r">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>

            @if ($transactions->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="5" class="r">Total</td>
                        <td class="r">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="foot">
            <div>
                Dicetak oleh {{ auth()->user()->name }}<br>
                {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
            </div>

            <div class="r">
                Apotek MedikaCare<br>
                Sehat Bersama, Lebih Baik
            </div>
        </div>
    </div>
</body>
</html>