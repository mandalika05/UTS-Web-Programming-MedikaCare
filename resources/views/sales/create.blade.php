@extends('layouts.app')
@section('title', 'Penjualan Baru')
@section('page_title', 'Penjualan Baru')
@section('page_icon', 'bi-cart-check')
@section('page_sub', 'Kasir: catat penjualan langsung')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle"></i> Penjualan belum bisa disimpan:</div>
            <ul class="mb-0 ps-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
        @csrf
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="mc-card reveal">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pelanggan terdaftar (opsional)</label>
                            <select name="customer_id" id="customerSelect" class="form-select">
                                <option value="">Pelanggan umum / tidak terdaftar</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama pelanggan (jika tidak terdaftar)</label>
                            <input type="text" name="customer_name" id="customerName"
                                   value="{{ old('customer_name') }}" maxlength="100"
                                   class="form-control" placeholder="Contoh: Ibu Ani">
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Metode pembayaran</label>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="radio" class="btn-check" name="payment_method" id="payCash"
                                       value="tunai" @checked(old('payment_method', 'tunai') === 'tunai')>
                                <label class="pay-pill" for="payCash"><i class="bi bi-cash-coin"></i> Tunai</label>

                                <input type="radio" class="btn-check" name="payment_method" id="payTransfer"
                                       value="transfer" @checked(old('payment_method') === 'transfer')>
                                <label class="pay-pill" for="payTransfer"><i class="bi bi-bank"></i> Transfer</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-capsule text-mc"></i> Daftar produk</h6>
                        <button type="button" id="addRow" class="btn-soft">
                            <i class="bi bi-plus-lg"></i> Tambah produk
                        </button>
                    </div>

                    <div id="items"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sum-card reveal" style="animation-delay:.1s">
                    <div class="fw-bold mb-2">Ringkasan penjualan</div>
                    <div class="sum-row"><span class="opacity-75">Jenis produk</span><strong id="sumItems">0</strong></div>
                    <div class="sum-row"><span class="opacity-75">Total unit</span><strong id="sumUnits">0</strong></div>
                    <div class="pt-3 pb-3">
                        <div class="small opacity-75">Total bayar</div>
                        <div class="sum-total" id="sumTotal">Rp 0</div>
                    </div>
                    <button type="submit" class="btn-light-mc" id="submitBtn">
                        <i class="bi bi-check2-circle"></i> Simpan penjualan
                    </button>
                    <div class="small opacity-75 mt-3">
                        Harga diambil dari database dan total dihitung ulang oleh server. Stok berkurang otomatis setelah penjualan disimpan.
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
@php
    $initialItems = old('items', [['product_id' => '', 'quantity' => 1]]);
@endphp
<script>
(function () {
    var products = @json($products);
    var initial = @json($initialItems);
    var wrap = document.getElementById('items');
    var index = 0;

    function rupiah(n) {
        return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    }

    function findProduct(id) {
        for (var k = 0; k < products.length; k++) {
            if (String(products[k].id) === String(id)) return products[k];
        }
        return null;
    }

    function addRow(data) {
        data = data || {};
        var i = index++;
        var row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML =
            '<div class="cell-product"><label class="mini-lbl">Produk</label>' +
            '<select class="form-select f-product" name="items[' + i + '][product_id]" required></select>' +
            '<div class="form-text stock-hint"></div></div>' +
            '<div><label class="mini-lbl">Jumlah</label>' +
            '<input type="number" min="1" class="form-control f-qty" name="items[' + i + '][quantity]" required></div>' +
            '<div><label class="mini-lbl">Harga satuan</label><div class="price-val">-</div></div>' +
            '<div><label class="mini-lbl">Subtotal</label><div class="sub-val">Rp 0</div></div>' +
            '<div class="cell-remove"><button type="button" class="btn-remove" title="Hapus baris"><i class="bi bi-x-lg"></i></button></div>';

        var select = row.querySelector('.f-product');
        var qty = row.querySelector('.f-qty');

        // opsi dibuat lewat DOM supaya nama produk tidak pernah dibaca sebagai HTML
        select.appendChild(new Option('Pilih produk', ''));
        products.forEach(function (p) {
            var opt = new Option(p.stock > 0 ? p.label : p.label + ' (habis)', p.id);
            if (p.stock <= 0) opt.disabled = true;
            select.appendChild(opt);
        });

        select.value = data.product_id || '';
        qty.value = data.quantity || 1;

        select.addEventListener('change', recalc);
        qty.addEventListener('input', recalc);
        row.querySelector('.btn-remove').addEventListener('click', function () {
            row.remove();
            recalc();
        });

        wrap.appendChild(row);
        recalc();
    }

    function recalc() {
        var rows = wrap.querySelectorAll('.item-row');
        var total = 0, units = 0, kinds = 0;

        rows.forEach(function (row) {
            var p = findProduct(row.querySelector('.f-product').value);
            var q = parseFloat(row.querySelector('.f-qty').value) || 0;
            var hint = row.querySelector('.stock-hint');
            var price = p ? p.price : 0;
            var sub = q * price;

            row.querySelector('.price-val').textContent = p ? rupiah(price) : '-';
            row.querySelector('.sub-val').textContent = rupiah(sub);

            if (p) {
                if (q > p.stock) {
                    hint.textContent = 'Melebihi stok (tersedia ' + p.stock + ')';
                    hint.className = 'form-text stock-hint text-danger';
                } else {
                    hint.textContent = 'Tersedia: ' + p.stock;
                    hint.className = 'form-text stock-hint';
                }
                kinds++;
            } else {
                hint.textContent = '';
            }

            total += sub;
            units += q;
            row.querySelector('.btn-remove').disabled = rows.length === 1;
        });

        document.getElementById('sumItems').textContent = kinds;
        document.getElementById('sumUnits').textContent = units;
        document.getElementById('sumTotal').textContent = rupiah(total);
    }

    // pelanggan terdaftar dipilih: kolom nama manual dikunci
    var customerSelect = document.getElementById('customerSelect');
    var customerName = document.getElementById('customerName');
    function syncCustomer() {
        customerName.disabled = customerSelect.value !== '';
        if (customerName.disabled) customerName.value = '';
    }
    customerSelect.addEventListener('change', syncCustomer);
    syncCustomer();

    document.getElementById('addRow').addEventListener('click', function () { addRow(); });

    // cegah klik ganda yang bisa membuat transaksi tercatat dua kali
    document.getElementById('saleForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

    (Array.isArray(initial) ? initial : Object.values(initial)).forEach(addRow);
})();
</script>
@endpush