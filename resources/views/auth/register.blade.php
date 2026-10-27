
@extends('layouts.guest')
@section('title', 'Daftar')

@section('content')
    <h3 class="fw-bold text-mc mb-1">Buat akun</h3>
    <p class="text-muted mb-4">Daftar untuk mulai menggunakan MedikaCare.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nomor telepon (opsional)</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Password (minimal 8 karakter)</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Ulangi password</label>
            <input type="password" name="password_confirmation"
                   class="form-control" required>
        </div>
        <button class="btn btn-mc w-100">Daftar</button>
    </form>

    <p class="text-center mt-4 mb-0">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-mc fw-semibold">Masuk</a>
    </p>
@endsection
