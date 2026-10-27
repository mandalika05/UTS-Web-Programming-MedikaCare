@extends('layouts.app')
@section('title', 'Kategori')
@section('page_title', 'Kategori Produk')

@section('content')
    <div class="mc-card">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control" placeholder="Cari kategori...">
                <button class="btn btn-mc">Cari</button>
            </form>

            <a href="{{ route('categories.create') }}" class="btn btn-mc">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama kategori</th>
                        <th>Jumlah produk</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="btn-soft text-decoration-none">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>

                                <form method="POST"
                                      action="{{ route('categories.destroy', $category) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-soft text-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada data kategori.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $categories->links() }}</div>
    </div>
@endsection