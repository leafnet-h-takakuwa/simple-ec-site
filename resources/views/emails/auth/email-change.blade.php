@extends('emails.layouts.shop')

@section('content')
メールアドレスの変更申請を受け付けました。

以下のURLにアクセスして、新しいメールアドレスの確認を完了してください。

{!! $url !!}

※ このURLの有効期限は60分です。
※ お心当たりがない場合は、このメールを破棄してください。
@endsection