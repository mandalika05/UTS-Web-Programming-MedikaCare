@php($s = $supplier ?? null)

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nama supplier</label>
        <input type="text" name="name" value="{{ old('name', $s?->name) }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Contoh: PT Sehat Farma Distribusi" required>
        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Nomor telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $s?->phone) }}"
               class="form-control @error('phone') is-invalid @enderror"
               placeholder="021-555-0101">
        @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nama kontak</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $s?->contact_person) }}"
               class="form-control @error('contact_person') is-invalid @enderror"
               placeholder="Contoh: Bagian Penjualan">
        @error('contact_person') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="3"
                  class="form-control @error('address') is-invalid @enderror"
                  style="border-radius:18px"
                  placeholder="Alamat lengkap supplier">{{ old('address', $s?->address) }}</textarea>
        @error('address') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-mc px-4">Simpan</button>
    <a href="{{ route('suppliers.index') }}"
       class="btn btn-soft d-inline-flex align-items-center text-decoration-none">Batal</a>
</div>
