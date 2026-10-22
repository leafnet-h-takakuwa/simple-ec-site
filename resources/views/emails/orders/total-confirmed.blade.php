@extends('emails.layouts.shop')

@section('content')
{{ $order->orderer_name }} 様

この度はご注文いただきまして誠にありがとうございます。
送料・代金引換手数料を含めたお支払い金額が確定いたしましたので、ご案内いたします。

以下のご注文内容をご確認くださいますようお願いいたします。

===========================================
ご注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.item-lines')
@include('emails.orders.partials.confirmed-total')

@include('emails.orders.partials.customer')

**************************************
【お支払いについて】

お支払い方法：代金引換

商品のお届け時に、配達員へ上記のお支払い金額をお支払いください。

**************************************

商品は発送準備が整い次第、発送いたします。
発送の際には、改めてメールにてご案内いたしますので、
今しばらくお待ちくださいますようお願いいたします。
@endsection