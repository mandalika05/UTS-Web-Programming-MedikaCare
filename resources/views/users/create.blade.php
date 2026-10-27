@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('page_title', 'Tambah Pengguna')
@section('page_icon', 'bi-person-plus')
@section('page_sub', 'Buat akun Admin, Staff, atau Pelanggan')

@section('content')
    <div class="mc-card reveal" style="max-width:860px">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')
        </form>
    </div>
@endsection