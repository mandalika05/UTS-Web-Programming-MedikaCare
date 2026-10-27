
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'MedikaCare')) | MedikaCare</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-transparan.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/medikacare.css') }}">
</head>
<body>

<div class="container-fluid p-0 auth-page">
    <div class="row g-0 min-vh-100">

        <!-- Panel kiri -->
        <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center">
            <div class="auth-left-inner">

                <img
                    class="brand-logo"
                    src="{{ asset('images/logo-transparan.png') }}"
                    alt="Apotek MedikaCare"
                >

                <h1 class="hero-title">
                    Kebutuhan sehatmu,<br>
                    <span class="accent">lengkap dalam satu tempat.</span>
                </h1>

                <p class="hero-sub">
                    Cek ketersediaan obat dan alat kesehatan, pesan dengan mudah,
                    lalu pantau statusnya sampai selesai.
                </p>

                <div class="d-flex flex-wrap gap-3">

                    <div class="pcard">
                        <div class="ico">
                            <i class="bi bi-capsule"></i>
                        </div>
                        <div>
                            <div class="nm">Paracetamol 500 mg</div>
                            <div class="st">Stok 120</div>
                            <span class="badge-s b-ok">Tersedia</span>
                        </div>
                    </div>

                    <div class="pcard">
                        <div class="ico">
                            <i class="bi bi-thermometer-half"></i>
                        </div>
                        <div>
                            <div class="nm">Termometer Digital</div>
                            <div class="st">Stok 25</div>
                            <span class="badge-s b-ok">Tersedia</span>
                        </div>
                    </div>

                    <div class="pcard">
                        <div class="ico">
                            <i class="bi bi-droplet-half"></i>
                        </div>
                        <div>
                            <div class="nm">Hand Sanitizer 100 ml</div>
                            <div class="st">Stok 3</div>
                            <span class="badge-s b-low">Hampir habis</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Panel kanan -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
            <div style="width:100%; max-width:450px">

                <div class="auth-card">

                    <div class="text-center d-lg-none mb-2">
                        <img
                            class="mobile-logo"
                            src="{{ asset('images/logo-transparan.png') }}"
                            alt="Apotek MedikaCare"
                        >
                    </div>

                    <div class="card-icon d-none d-lg-grid">
                        <i class="bi bi-heart-pulse"></i>
                    </div>

                    @yield('content')

                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
