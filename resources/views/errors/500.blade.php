@extends('layouts.app')

@section('title', 'エラーが発生しました')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-20 text-center">
        <h1 class="text-2xl font-bold">
            申し訳ございません
        </h1>

        <p class="mt-6 text-slate-600">
            システムエラーが発生しました。<br>
            しばらく時間をおいてから、再度お試しください。
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary mt-8">
            トップページへ戻る
        </a>
    </div>
@endsection
