{{-- 注文明細 --}}
@foreach ($order->orderItems as $item)
● {{ $item->product_name }}{{ filled($item->volume) ? ' ' . $item->volume : '' }}{{ $item->tax_rate === 8 ? ' (※)' : '' }}
　　価格：{{ number_format($item->price) }} 円 (1個あたり)
　　数量：{{ number_format($item->quantity) }} 個
　　小計：{{ number_format($item->subtotal) }} 円

@endforeach
