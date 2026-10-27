@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('page_title', 'Tambah Supplier')
@section('page_icon', 'bi-plus-circle')
@section('page_sub', 'Daftarkan pemasok baru')

@section('content')
    <div class="mc-card reveal" style="max-width:860px">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            @include('suppliers._form')
        </form>
    </div>
@endsection
