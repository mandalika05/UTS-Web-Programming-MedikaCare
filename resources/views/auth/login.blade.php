
@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <h3 class="fw-bold text-mc mb-1">Selamat datang kembali</h3>
    <p class="text-muted mb-4">
        Masuk untuk memesan atau mengelola apotek.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
                Email
            </label>

            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control"
                    placeholder="nama@email.com"
                    required
                    autofocus
                >
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">
                Password
            </label>

            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required
                >

                <span
                    class="input-group-text"
                    id="togglePw"
                    title="Lihat password"
                    style="cursor: pointer;"
                >
                    <i class="bi bi-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-mc w-100">
            Masuk <i class="bi bi-arrow-right"></i>
        </button>
    </form>

    @env('local')
        <div class="mt-4 pt-3 border-top">
            <div class="small text-muted mb-2">
                Akun demo (mode lokal):
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button
                    type="button"
                    class="btn-soft"
                    onclick="fillDemo('admin@medikacare.test')"
                >
                    Admin
                </button>

                <button
                    type="button"
                    class="btn-soft"
                    onclick="fillDemo('staff1@medikacare.test')"
                >
                    Staff
                </button>

                <button
                    type="button"
                    class="btn-soft"
                    onclick="fillDemo('pelanggan@medikacare.test')"
                >
                    Pelanggan
                </button>
            </div>
        </div>
    @endenv

    <p class="text-center mt-4 mb-0">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-mc fw-semibold">
            Daftar sekarang
        </a>
    </p>
@endsection

@push('scripts')
<script>
    function fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }

    const togglePw = document.getElementById('togglePw');

    if (togglePw) {
        togglePw.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');

            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
</script>
@endpush
