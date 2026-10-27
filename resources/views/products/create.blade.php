@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page_title', 'Tambah Produk')
@section('page_icon', 'bi-plus-circle')
@section('page_sub', 'Daftarkan obat atau alat kesehatan baru')

@section('content')
    <div class="mc-card reveal" style="max-width: 860px">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            @include('products._form')
        </form>
    </div>
@endsection
