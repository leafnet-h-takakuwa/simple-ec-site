@extends('layouts.app')

@section('content')
    <div class="prose hero bg-base-200 mx-auto max-w-full rounded">
        <div class="hero-content text-center my-10">
            <div class="max-w-md mb-10">
                <h2>Welcome to the Microposts</h2>
                {{-- ユーザー登録ページへのリンク --}}
                <a class="btn btn-neutral btn-lg" href="{{ route('register') }}">Sign up now!</a>
            </div>
        </div>
    </div>
@endsection
