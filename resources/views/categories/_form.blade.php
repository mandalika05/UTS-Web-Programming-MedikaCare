<div class="mb-4">
    <label class="form-label fw-semibold">Nama kategori</label>
    <input type="text" name="name"
           value="{{ old('name', $category->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror"
           placeholder="Contoh: Obat Bebas" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex gap-2">
    <button class="btn btn-mc">Simpan</button>
    <a href="{{ route('categories.index') }}" class="btn btn-soft">Batal</a>
</div>