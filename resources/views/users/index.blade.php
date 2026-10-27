@extends('layouts.app')
@section('title', 'Pengguna')
@section('page_title', 'Pengguna')
@section('page_icon', 'bi-people')
@section('page_sub', 'Kelola akun Admin, Staff, dan Pelanggan')

@section('content')
    <div class="mc-card reveal">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            @php($base = array_filter(['q' => request('q')]))
            <div class="pill-tabs">
                <a href="{{ route('users.index', $base) }}"
                   class="pill-tab {{ request('role') ? '' : 'active' }}">
                    Semua <span class="cnt">{{ $countAll }}</span>
                </a>
                <a href="{{ route('users.index', $base + ['role' => 'admin']) }}"
                   class="pill-tab {{ request('role') === 'admin' ? 'active' : '' }}">
                    Admin <span class="cnt">{{ $countAdmin }}</span>
                </a>
                <a href="{{ route('users.index', $base + ['role' => 'staff']) }}"
                   class="pill-tab {{ request('role') === 'staff' ? 'active' : '' }}">
                    Staff <span class="cnt">{{ $countStaff }}</span>
                </a>
                <a href="{{ route('users.index', $base + ['role' => 'customer']) }}"
                   class="pill-tab {{ request('role') === 'customer' ? 'active' : '' }}">
                    Pelanggan <span class="cnt">{{ $countCustomer }}</span>
                </a>
            </div>

            <a href="{{ route('users.create') }}" class="btn btn-mc">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </a>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2 mb-4" style="max-width:520px">
            @if (request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control" placeholder="Cari nama atau email...">
            </div>
            <button class="btn btn-mc">Cari</button>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Telepon</th>
                        <th>Peran</th>
                        <th>Bergabung</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="sup-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id())
                                                <span class="small text-muted">(Anda)</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td>
                                <span class="role-badge {{ $user->role === 'admin' ? 'role-admin' : ($user->role === 'customer' ? 'role-customer' : '') }}">
                                    {{ $user->roleLabel() }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $user->created_at->locale('id')->translatedFormat('d M Y') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="btn-soft text-decoration-none">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-soft text-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-box">
                                    <i class="bi bi-people"></i>
                                    Pengguna tidak ditemukan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $users->links() }}</div>
    </div>
@endsection