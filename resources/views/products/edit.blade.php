@extends('layouts.app')

@section('title', 'Ubah Produk')
@section('page_title', 'Ubah Produk')
@section('page_icon', 'bi-pencil-square')
@section('page_sub', $product->name)

@section('content')
    <div class="mc-card reveal" style="max-width: 860px">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')

            @include('products._form')
        </form>
    </div>
@endsection
