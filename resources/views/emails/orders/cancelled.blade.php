@extends('emails.layouts.shop')

@section('content')
{{ $order->orderer_name }} 様

先日は「{{ config('company.shop_name') }}」をご利用いただき、
誠にありがとうございます。

お客様からのご注文をキャンセルとさせていただきました。

以下のご注文内容のキャンセルについてお心当たりがない場合は、
お手数をおかけしますが当店までご連絡をお願いいたします。
（本メール下部に電話番号・メールアドレスがございます）

またの機会がございましたらよろしくお願いいたします。
ご利用ありがとうございました。

===========================================
ご注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.item-lines')
@include('emails.orders.partials.customer')
@endsection