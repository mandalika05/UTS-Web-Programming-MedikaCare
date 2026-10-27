@extends('layouts.app')
@section('title', 'Ubah Pengguna')
@section('page_title', 'Ubah Pengguna')
@section('page_icon', 'bi-pencil-square')
@section('page_sub', $user->email)

@section('content')
    <div class="mc-card reveal" style="max-width:860px">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form')
        </form>
    </div>
@endsection