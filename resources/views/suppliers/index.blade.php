@extends('layouts.app')
@section('title', 'Supplier')
@section('page_title', 'Supplier')
@section('page_icon', 'bi-truck')
@section('page_sub', 'Pemasok obat dan alat kesehatan')

@section('content')
    <div class="mc-card reveal">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <form method="GET" action="{{ route('suppliers.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width:520px">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Cari nama, kontak, atau telepon...">
                </div>
                <button class="btn btn-mc">Cari</button>
            </form>

            <a href="{{ route('suppliers.create') }}" class="btn btn-mc">
                <i class="bi bi-plus-lg"></i> Tambah Supplier
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Pembelian</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="sup-avatar">{{ mb_strtoupper(mb_substr($supplier->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $supplier->name }}</div>
                                        <div class="small text-muted">{{ $supplier->contact_person ?: '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td class="small text-muted" style="max-width:240px">
                                {{ \Illuminate\Support\Str::limit($supplier->address ?: '-', 50) }}
                            </td>
                            <td>
                                <span class="role-badge">{{ $supplier->purchases_count }} transaksi</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('suppliers.edit', $supplier) }}"
                                   class="btn-soft text-decoration-none">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus supplier ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-soft text-danger"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-box">
                                    <i class="bi bi-truck"></i>
                                    Belum ada supplier yang cocok.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $suppliers->links() }}</div>
    </div>
@endsection
