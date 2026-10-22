@extends('emails.layouts.shop')

@section('content')
{{ $order->orderer_name }} 様

この度は「{{ config('company.shop_name') }}」をご利用いただきまして、誠にありがとうございます。
お客様からのご注文を以下の通り承りました。
ご確認後、内容に間違いがございましたらお早めに当店までご連絡をお願いいたします。
(本メール下部に電話番号・メールアドレスがございます）

@if ($order->payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
また、以下の合計金額には送料や代引手数料は含まれておりません。
@else
また、以下の合計金額には送料は含まれておりません。
@endif
送料が確定しましたら再度メールにてご案内いたしますので、
今しばらくお待ちくださるようお願いいたします。

===========================================
ご注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.items')

@include('emails.orders.partials.customer')
@endsection
