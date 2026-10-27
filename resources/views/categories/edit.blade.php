@extends('layouts.app')
@section('title', 'Ubah Kategori')
@section('page_title', 'Ubah Kategori')

@section('content')
    <div class="mc-card" style="max-width:560px">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('categories._form')
        </form>
    </div>
@endsection