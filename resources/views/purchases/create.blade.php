@extends('layouts.app')
@section('title', 'Pembelian Baru')
@section('page_title', 'Pembelian Baru')
@section('page_icon', 'bi-box-arrow-in-down')
@section('page_sub', 'Catat stok masuk dari supplier')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle"></i> Pembelian belum bisa disimpan:</div>
            <ul class="mb-0 ps-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
        @csrf
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="mc-card reveal">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>

                    <hr class="my-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-capsule text-mc"></i> Daftar produk</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" id="addRow" class="btn-soft">
                                <i class="bi bi-plus-lg"></i> Tambah produk
                            </button>
                            <button type="button" id="addNewRow" class="btn-soft">
                                <i class="bi bi-stars"></i> Tambah produk baru
                            </button>
                        </div>
                    </div>

                    <div id="items"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sum-card reveal" style="animation-delay:.1s">
                    <div class="fw-bold mb-2">Ringkasan pembelian</div>
                    <div class="sum-row"><span class="opacity-75">Jenis produk</span><strong id="sumItems">0</strong></div>
                    <div class="sum-row"><span class="opacity-75">Total unit</span><strong id="sumUnits">0</strong></div>
                    <div class="pt-3 pb-3">
                        <div class="small opacity-75">Total pembelian</div>
                        <div class="sum-total" id="sumTotal">Rp 0</div>
                    </div>
                    <button type="submit" class="btn-light-mc" id="submitBtn">
                        <i class="bi bi-check2-circle"></i> Simpan pembelian
                    </button>
                    <div class="small opacity-75 mt-3">
                        Stok produk bertambah otomatis setelah pembelian disimpan. Produk baru otomatis masuk ke daftar produk dengan SKU otomatis. Total dihitung ulang oleh server.
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
@php
    $initialItems = old('items', [['mode' => 'existing', 'product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
@endphp
<script>
(function () {
    var products = @json($products);
    var categories = @json($categories);
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
            '<input type="hidden" class="f-mode" name="items[' + i + '][mode]" value="existing">' +
            '<div class="mode-switch">' +
            '<button type="button" class="mode-btn active" data-mode="existing">Produk yang ada</button>' +
            '<button type="button" class="mode-btn" data-mode="new">Produk baru</button>' +
            '</div>' +
            '<select class="form-select f-product" name="items[' + i + '][product_id]" required></select>' +
            '<div class="form-text stock-hint"></div>' +
            '<div class="form-text new-hint d-none">SKU dibuat otomatis saat disimpan.</div></div>' +
            '<div><label class="mini-lbl">Jumlah</label>' +
            '<input type="number" min="1" class="form-control f-qty" name="items[' + i + '][quantity]" required></div>' +
            '<div><label class="mini-lbl">Harga beli satuan</label>' +
            '<input type="number" min="0" step="1" class="form-control f-cost" name="items[' + i + '][unit_cost]" required></div>' +
            '<div><label class="mini-lbl">Subtotal</label><div class="sub-val">Rp 0</div></div>' +
            '<div class="cell-remove"><button type="button" class="btn-remove" title="Hapus baris"><i class="bi bi-x-lg"></i></button></div>' +
            '<div class="new-box d-none"><div class="row g-2">' +
            '<div class="col-md-5"><label class="mini-lbl">Nama produk baru</label>' +
            '<input type="text" class="form-control f-new-name" name="items[' + i + '][new_name]" maxlength="150" placeholder="Contoh: Obat Batuk Anak 60 ml" disabled></div>' +
            '<div class="col-md-3"><label class="mini-lbl">Kategori</label>' +
            '<select class="form-select f-new-cat" name="items[' + i + '][new_category_id]" disabled></select></div>' +
            '<div class="col-md-2"><label class="mini-lbl">Harga jual</label>' +
            '<input type="number" min="0" step="1" class="form-control f-new-sell" name="items[' + i + '][new_selling_price]" disabled></div>' +
            '<div class="col-md-2"><label class="mini-lbl">Stok min.</label>' +
            '<input type="number" min="0" class="form-control f-new-min" name="items[' + i + '][new_minimum_stock]" value="10" disabled></div>' +
            '</div></div>';

        var modeInput = row.querySelector('.f-mode');
        var select = row.querySelector('.f-product');
        var qty = row.querySelector('.f-qty');
        var cost = row.querySelector('.f-cost');
        var hint = row.querySelector('.stock-hint');
        var newHint = row.querySelector('.new-hint');
        var newBox = row.querySelector('.new-box');
        var newName = row.querySelector('.f-new-name');
        var newCat = row.querySelector('.f-new-cat');
        var newSell = row.querySelector('.f-new-sell');
        var newMin = row.querySelector('.f-new-min');

        // opsi dibuat lewat DOM supaya nama produk tidak pernah dibaca sebagai HTML
        select.appendChild(new Option('Pilih produk', ''));
        products.forEach(function (p) {
            select.appendChild(new Option(p.label, p.id));
        });

        newCat.appendChild(new Option('Pilih kategori', ''));
        categories.forEach(function (c) {
            newCat.appendChild(new Option(c.name, c.id));
        });

        select.value = data.product_id || '';
        qty.value = data.quantity || 1;
        cost.value = data.unit_cost || '';
        if (data.unit_cost) cost.dataset.touched = '1';

        newName.value = data.new_name || '';
        newCat.value = data.new_category_id || '';
        newSell.value = data.new_selling_price || '';
        if (data.new_minimum_stock !== undefined && data.new_minimum_stock !== '') {
            newMin.value = data.new_minimum_stock;
        }

        function showHint() {
            var p = findProduct(select.value);
            hint.textContent = p ? 'Stok saat ini: ' + p.stock : '';
        }

        function setMode(mode) {
            var isNew = mode === 'new';
            modeInput.value = mode;

            row.querySelectorAll('.mode-btn').forEach(function (b) {
                b.classList.toggle('active', b.dataset.mode === mode);
            });

            // kolom yang tidak dipakai dimatikan, supaya tidak ikut terkirim ke server
            select.classList.toggle('d-none', isNew);
            select.disabled = isNew;
            select.required = !isNew;
            hint.classList.toggle('d-none', isNew);
            newHint.classList.toggle('d-none', !isNew);
            newBox.classList.toggle('d-none', !isNew);

            [newName, newCat, newSell, newMin].forEach(function (el) {
                el.disabled = !isNew;
                el.required = isNew;
            });
        }

        row.querySelectorAll('.mode-btn').forEach(function (b) {
            b.addEventListener('click', function () {
                setMode(b.dataset.mode);
                recalc();
            });
        });

        select.addEventListener('change', function () {
            var p = findProduct(select.value);
            if (p && cost.dataset.touched !== '1') cost.value = p.cost;
            showHint();
            recalc();
        });
        newName.addEventListener('input', recalc);
        cost.addEventListener('input', function () { cost.dataset.touched = '1'; recalc(); });
        qty.addEventListener('input', recalc);
        row.querySelector('.btn-remove').addEventListener('click', function () {
            row.remove();
            recalc();
        });

        wrap.appendChild(row);
        setMode(data.mode === 'new' ? 'new' : 'existing');
        showHint();
        recalc();
    }

    function recalc() {
        var rows = wrap.querySelectorAll('.item-row');
        var total = 0, units = 0, kinds = 0;

        rows.forEach(function (row) {
            var isNew = row.querySelector('.f-mode').value === 'new';
            var q = parseFloat(row.querySelector('.f-qty').value) || 0;
            var c = parseFloat(row.querySelector('.f-cost').value) || 0;
            var sub = q * c;
            row.querySelector('.sub-val').textContent = rupiah(sub);
            total += sub;
            units += q;

            if (isNew) {
                if (row.querySelector('.f-new-name').value.trim() !== '') kinds++;
            } else if (row.querySelector('.f-product').value) {
                kinds++;
            }

            row.querySelector('.btn-remove').disabled = rows.length === 1;
        });

        document.getElementById('sumItems').textContent = kinds;
        document.getElementById('sumUnits').textContent = units;
        document.getElementById('sumTotal').textContent = rupiah(total);
    }

    document.getElementById('addRow').addEventListener('click', function () { addRow(); });
    document.getElementById('addNewRow').addEventListener('click', function () { addRow({ mode: 'new' }); });

    // cegah klik ganda yang bisa membuat pembelian tercatat dua kali
    document.getElementById('purchaseForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

    (Array.isArray(initial) ? initial : Object.values(initial)).forEach(addRow);
})();
</script>
@endpush