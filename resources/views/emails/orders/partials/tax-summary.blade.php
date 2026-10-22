{{-- 税率別の消費税内訳 --}}
@foreach ($order->taxSummaries->sortBy('tax_rate') as $summary)
{{ $summary->tax_rate }}%対象：{{ number_format($summary->tax_included_amount) }} 円
　内消費税：{{ number_format($summary->tax_amount) }} 円
@endforeach
