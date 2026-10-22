
@extends('emails.layouts.shop')

@section('content')
以下のお申込みがありました。
内容を確認して対応してください。

{{ route('admin.orders.show', $order->id) }}
===========================================
注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.items')

@include('emails.orders.partials.customer')
@endsection
