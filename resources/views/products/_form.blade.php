@php($p = $product ?? null)

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input type="text" name="sku"
               value="{{ old('sku', $p?->sku) }}"
               class="form-control @error('sku') is-invalid @enderror"
               placeholder="Contoh: OBT-010" required>
        @error('sku')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text">Kode unik untuk setiap produk.</div>
    </div>

    <div class="col-md-8">
        <label class="form-label">Nama produk</label>
        <input type="text" name="name"
               value="{{ old('name', $p?->name) }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Contoh: Paracetamol 500 mg" required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <select name="category_id"
                class="form-select @error('category_id') is-invalid @enderror"
                required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $p?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Stok minimum</label>
        <input type="number" min="0" name="minimum_stock"
               value="{{ old('minimum_stock', $p?->minimum_stock ?? 10) }}"
               class="form-control @error('minimum_stock') is-invalid @enderror"
               required>
        @error('minimum_stock')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text">Batas untuk menandai stok rendah.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Harga beli</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" min="0" step="1" name="purchase_price"
                   value="{{ old('purchase_price', isset($p) ? (int) $p->purchase_price : '') }}"
                   class="form-control @error('purchase_price') is-invalid @enderror"
                   required>
        </div>
        @error('purchase_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Harga jual</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" min="0" step="1" name="selling_price"
                   value="{{ old('selling_price', isset($p) ? (int) $p->selling_price : '') }}"
                   class="form-control @error('selling_price') is-invalid @enderror"
                   required>
        </div>
        @error('selling_price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">
            {{ $p ? 'Stok saat ini' : 'Stok awal' }}
        </label>

        @if ($p)
            <input type="text" class="form-control"
                   value="{{ $p->stock }}" disabled>
            <div class="form-text">
                Stok saat ini ditampilkan saja dan tidak diubah dari formulir ini.
            </div>
        @else
            <input type="number" min="0" name="stock"
                   value="{{ old('stock', 0) }}"
                   class="form-control @error('stock') is-invalid @enderror"
                   required>
            @error('stock')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        @endif
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-mc px-4">
        <i class="bi bi-check-lg"></i> Simpan
    </button>

    <a href="{{ route('products.index') }}"
       class="btn btn-soft d-inline-flex align-items-center text-decoration-none">
        Batal
    </a>
</div>
