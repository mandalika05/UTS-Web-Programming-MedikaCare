@extends('layouts.shop')
@section('title', 'Keranjang')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4 reveal">
        <div class="tb-ico"><i class="bi bi-bag"></i></div>
        <div>
            <div class="tb-title">Keranjang belanja</div>
            <div class="tb-sub">Periksa pesananmu sebelum dikirim ke apotek</div>
        </div>
    </div>

    @if (count($lines) === 0)
        <div class="mc-card reveal">
            <div class="empty-box">
                <i class="bi bi-bag"></i>
                Keranjang masih kosong.
                <div class="mt-3">
                    <a href="{{ route('shop.index') }}" class="btn btn-mc">Lihat katalog</a>
                </div>
            </div>
        </div>
    @else
        <div class="row g-3">
            <div class="col-lg-8">
                @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <div class="cart-line reveal">
                        <div class="shop-ico"><i class="bi bi-capsule"></i></div>
                        <div class="cl-main">
                            <div class="fw-bold">{{ $product->name }}</div>
                            <div class="small text-muted">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }} per unit
                            </div>
                            @if ($line['over'])
                                <div class="small text-danger">
                                    Melebihi stok (tersedia {{ $product->stock }}), sesuaikan jumlahnya.
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('cart.update', $product->id) }}" class="cl-qty">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity" value="{{ $line['quantity'] }}"
                                   min="1" max="100" class="form-control">
                            <button class="btn-soft">Ubah</button>
                        </form>

                        <div class="cl-sub">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</div>

                        <form method="POST" action="{{ route('cart.remove', $product->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-remove" title="Hapus dari keranjang">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                <form method="POST" action="{{ route('cart.checkout') }}" id="checkoutForm">
                    @csrf
                    <div class="checkout-card reveal" style="animation-delay:.1s">
                        <div class="co-head"><i class="bi bi-receipt-cutoff"></i> Ringkasan pesanan</div>

                        <div class="co-row">
                            <span>Jenis produk</span>
                            <strong>{{ count($lines) }}</strong>
                        </div>
                        <div class="co-row">
                            <span>Total unit</span>
                            <strong>{{ collect($lines)->sum('quantity') }}</strong>
                        </div>

                        <div class="co-total">
                            <span>Total bayar</span>
                            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                        </div>

                        <div class="co-label">Metode pembayaran</div>

                        <input type="radio" class="btn-check" name="payment_method" id="payCash"
                               value="tunai" @checked(old('payment_method', 'tunai') === 'tunai')>
                        <label class="pay-option" for="payCash">
                            <span class="po-ico"><i class="bi bi-shop"></i></span>
                            <span class="po-text">
                                <strong>Bayar di toko</strong>
                                <small>Tunai saat mengambil di apotek</small>
                            </span>
                        </label>

                        <input type="radio" class="btn-check" name="payment_method" id="payTransfer"
                               value="transfer" @checked(old('payment_method') === 'transfer')>
                        <label class="pay-option" for="payTransfer">
                            <span class="po-ico"><i class="bi bi-bank"></i></span>
                            <span class="po-text">
                                <strong>Transfer bank</strong>
                                <small>Transfer, lalu tunggu konfirmasi</small>
                            </span>
                        </label>

                        <button type="submit" class="btn-light-mc" id="checkoutBtn">
                            <i class="bi bi-check2-circle"></i> Buat pesanan
                        </button>
                        <div class="co-note">
                            Stok baru berkurang setelah pesananmu dikonfirmasi apotek. Harga dihitung ulang oleh server.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    var form = document.getElementById('checkoutForm');
    if (form) {
        form.addEventListener('submit', function () {
            var btn = document.getElementById('checkoutBtn');
            btn.disabled = true;
            btn.textContent = 'Membuat pesanan...';
        });
    }
</script>
@endpush
