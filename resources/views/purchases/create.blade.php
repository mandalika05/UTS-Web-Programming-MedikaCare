@extends('layouts.app')
@section('title', 'Pembelian Baru')
@section('page_title', 'Pembelian Baru')
@section('page_icon', 'bi-box-arrow-in-down')
@section('page_sub', 'Catat stok masuk dari supplier')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <div class="fw-bold mb-1">
                <i class="bi bi-exclamation-triangle"></i>
                Pembelian belum bisa disimpan:
            </div>
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
                            <option value="{{ $supplier->id }}"
                                @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-capsule text-mc"></i>
                            Daftar produk
                        </h6>
                        <button type="button" id="addRow" class="btn-soft">
                            <i class="bi bi-plus-lg"></i>
                            Tambah produk
                        </button>
                    </div>

                    <div id="items"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sum-card reveal" style="animation-delay:.1s">
                    <div class="fw-bold mb-2">Ringkasan pembelian</div>

                    <div class="sum-row">
                        <span class="opacity-75">Jenis produk</span>
                        <strong id="sumItems">0</strong>
                    </div>

                    <div class="sum-row">
                        <span class="opacity-75">Total unit</span>
                        <strong id="sumUnits">0</strong>
                    </div>

                    <div class="pt-3 pb-3">
                        <div class="small opacity-75">Total pembelian</div>
                        <div class="sum-total" id="sumTotal">Rp 0</div>
                    </div>

                    <button type="submit" class="btn-light-mc" id="submitBtn">
                        <i class="bi bi-check2-circle"></i>
                        Simpan pembelian
                    </button>

                    <div class="small opacity-75 mt-3">
                        Stok produk bertambah otomatis setelah pembelian disimpan.
                        Total dihitung ulang oleh server.
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
@php
    $initialItems = old('items', [
        [
            'product_id' => '',
            'quantity' => 1,
            'unit_cost' => '',
        ],
    ]);
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
            if (String(products[k].id) === String(id)) {
                return products[k];
            }
        }
        return null;
    }

    function addRow(data) {
        data = data || {};

        var i = index++;
        var row = document.createElement('div');
        row.className = 'item-row';

        row.innerHTML =
            '<div class="cell-product">' +
                '<label class="mini-lbl">Produk</label>' +
                '<select class="form-select f-product" ' +
                    'name="items[' + i + '][product_id]" required>' +
                '</select>' +
                '<div class="form-text stock-hint"></div>' +
            '</div>' +

            '<div>' +
                '<label class="mini-lbl">Jumlah</label>' +
                '<input type="number" min="1" ' +
                    'class="form-control f-qty" ' +
                    'name="items[' + i + '][quantity]" required>' +
            '</div>' +

            '<div>' +
                '<label class="mini-lbl">Harga beli satuan</label>' +
                '<input type="number" min="0" step="1" ' +
                    'class="form-control f-cost" ' +
                    'name="items[' + i + '][unit_cost]" required>' +
            '</div>' +

            '<div>' +
                '<label class="mini-lbl">Subtotal</label>' +
                '<div class="sub-val">Rp 0</div>' +
            '</div>' +

            '<div class="cell-remove">' +
                '<button type="button" class="btn-remove" title="Hapus baris">' +
                    '<i class="bi bi-x-lg"></i>' +
                '</button>' +
            '</div>';

        var select = row.querySelector('.f-product');
        var qty = row.querySelector('.f-qty');
        var cost = row.querySelector('.f-cost');
        var hint = row.querySelector('.stock-hint');

        select.appendChild(new Option('Pilih produk', ''));

        products.forEach(function (p) {
            select.appendChild(new Option(p.label, p.id));
        });

        select.value = data.product_id || '';
        qty.value = data.quantity || 1;
        cost.value = data.unit_cost || '';

        if (data.unit_cost !== undefined && data.unit_cost !== '') {
            cost.dataset.touched = '1';
        }

        function showHint() {
            var p = findProduct(select.value);
            hint.textContent = p ? 'Stok saat ini: ' + p.stock : '';
        }

        select.addEventListener('change', function () {
            var p = findProduct(select.value);

            if (p && cost.dataset.touched !== '1') {
                cost.value = p.cost;
            }

            showHint();
            recalc();
        });

        cost.addEventListener('input', function () {
            cost.dataset.touched = '1';
            recalc();
        });

        qty.addEventListener('input', recalc);

        row.querySelector('.btn-remove').addEventListener('click', function () {
            row.remove();
            recalc();
        });

        wrap.appendChild(row);
        showHint();
        recalc();
    }

    function recalc() {
        var rows = wrap.querySelectorAll('.item-row');
        var total = 0;
        var units = 0;
        var kinds = 0;

        rows.forEach(function (row) {
            var q = parseFloat(row.querySelector('.f-qty').value) || 0;
            var c = parseFloat(row.querySelector('.f-cost').value) || 0;
            var sub = q * c;

            row.querySelector('.sub-val').textContent = rupiah(sub);

            total += sub;
            units += q;

            if (row.querySelector('.f-product').value) {
                kinds++;
            }

            row.querySelector('.btn-remove').disabled = rows.length === 1;
        });

        document.getElementById('sumItems').textContent = kinds;
        document.getElementById('sumUnits').textContent = units;
        document.getElementById('sumTotal').textContent = rupiah(total);
    }

    document.getElementById('addRow').addEventListener('click', function () {
        addRow();
    });

    document.getElementById('purchaseForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

    (Array.isArray(initial) ? initial : Object.values(initial)).forEach(addRow);
})();
</script>
@endpush
