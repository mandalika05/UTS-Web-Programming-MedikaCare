
@extends('layouts.guest')
@section('title', 'Dashboard')

@section('content')
    <h3 class="fw-bold text-mc">Halo, {{ auth()->user()->name }}</h3>
    <p>Peran Anda: <strong>{{ auth()->user()->roleLabel() }}</strong></p>

    <p class="text-muted small">Uji akses:
        <a href="/uji/admin">/uji/admin</a> |
        <a href="/uji/staff">/uji/staff</a>
    </p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-mc">Keluar</button>
    </form>
@endsection
