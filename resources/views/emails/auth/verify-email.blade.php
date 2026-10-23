@extends('emails.layouts.shop')

@section('content')
会員登録ありがとうございます。

以下のURLにアクセスして、メールアドレスの認証を完了してください。

{!! $url !!}

※ このURLには有効期限があります。
※ お心当たりがない場合は、このメールを破棄してください。
@endsection