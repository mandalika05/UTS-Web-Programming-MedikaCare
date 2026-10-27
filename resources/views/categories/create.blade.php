@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('page_title', 'Tambah Kategori')

@section('content')
    <div class="mc-card" style="max-width:560px">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            @include('categories._form')
        </form>
    </div>
@endsection