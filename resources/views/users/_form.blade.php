@php($u = $user ?? null)
@php($isSelf = $u && $u->id === auth()->id())

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama lengkap</label>
        <input type="text" name="name" value="{{ old('name', $u?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $u?->email) }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nomor telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $u?->phone) }}"
               class="form-control @error('phone') is-invalid @enderror" placeholder="Opsional">
        @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Peran</label>
        @if ($isSelf)
            <input type="hidden" name="role" value="{{ $u->role }}">
            <input type="text" class="form-control" value="{{ $u->roleLabel() }}" disabled>
            <div class="form-text">Peran akun Anda sendiri tidak bisa diubah.</div>
        @else
            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Pilih peran</option>
                <option value="admin" @selected(old('role', $u?->role) === 'admin')>Admin</option>
                <option value="staff" @selected(old('role', $u?->role) === 'staff')>Staff</option>
                <option value="customer" @selected(old('role', $u?->role) === 'customer')>Pelanggan</option>
            </select>
        @endif
        @error('role') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ $u ? 'Password baru' : 'Password' }}</label>
        <input type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               {{ $u ? '' : 'required' }} autocomplete="new-password">
        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <div class="form-text">
            {{ $u ? 'Kosongkan jika password tidak diganti. Minimal 8 karakter.' : 'Minimal 8 karakter.' }}
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ulangi password</label>
        <input type="password" name="password_confirmation" class="form-control"
               {{ $u ? '' : 'required' }} autocomplete="new-password">
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-mc px-4">Simpan</button>
    <a href="{{ route('users.index') }}"
       class="btn btn-soft d-inline-flex align-items-center text-decoration-none">Batal</a>
</div>