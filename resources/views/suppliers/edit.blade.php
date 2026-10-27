@extends('layouts.app')
@section('title', 'Ubah Supplier')
@section('page_title', 'Ubah Supplier')
@section('page_icon', 'bi-pencil-square')
@section('page_sub', $supplier->name)

@section('content')
    <div class="mc-card reveal" style="max-width:860px">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            @include('suppliers._form')
        </form>
    </div>
@endsection
