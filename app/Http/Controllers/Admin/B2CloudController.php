<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderShipping;
use Illuminate\Http\Request;
use App\Enums\PaymentMethod;
use App\Services\B2CloudCsvService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class B2CloudController extends Controller
{
    public function index(Request $request)
    {
        $showRegistered = $request->boolean('show_registered');

        $shippings = OrderShipping::query()
            ->with(['order'])
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', [
                    'shipped',
                    'cancelled',
                ]);
            })
            ->when(!$showRegistered, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('tracking_number')
                        ->orWhere('tracking_number', '');
                });
            })
            ->orderBy('order_id')
            ->orderBy('id')
            ->paginate(config('shop.pagination.admin_b2_cloud_shippings'))
            ->withQueryString();

        return view('admin.b2-cloud.index', compact(
            'shippings',
            'showRegistered'
        ));
    }


    public function export(Request $request, B2CloudCsvService $csvService)
    {
        $validated = $request->validate([
            'shipping_date' => ['required', 'date_format:Y-m-d'],
            'shipping_ids' => ['required', 'array', 'min:1'],
            'shipping_ids.*' => ['required', 'integer', 'distinct', 'exists:order_shippings,id'],
        ]);

        $ids = $validated['shipping_ids'];

        $shippings = OrderShipping::with(['order.taxSummaries'])
            ->whereIn('id', $ids)
            ->orderBy('order_id')
            ->orderBy('id')
            ->get();

        if ($shippings->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'shipping_ids' => '選択した個口を取得できませんでした。',
            ]);
        }

        foreach ($shippings as $shipping) {
            $order = $shipping->order;

            if (!$order || in_array($order->status->value, ['shipped', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'shipping_ids' => '発送済み・キャンセル済みの個口は出力できません。',
                ]);
            }

            if (!$shipping->canExportB2()) {
                throw ValidationException::withMessages([
                    'shipping_ids' => '出力できない個口が含まれています。注文情報を確認してください。',
                ]);
            }
        }

        // 代引金額・内消費税額の計算

        $collectAmounts = [];

        foreach ($shippings->pluck('order')->unique('id') as $order) {
            if ($order->payment_method !== PaymentMethod::CASH_ON_DELIVERY) {
                continue;
            }

            // CSVの選択対象ではなく、注文全体の最小個口IDを取得
            $firstShippingId = $order->orderShippings()->min('id');

            // CSV出力処理で参照するために保持
            $order->first_shipping_id = $firstShippingId;

            $collectAmounts[$order->id] = [
                'amount' => (int) $order->total_amount
                    + (int) $order->shipping_fee
                    + (int) $order->cod_fee,

                'tax' => (int) $order->taxSummaries->sum('tax_amount'),
            ];
        }



        $csv = $csvService->generate(
            $shippings,
            str_replace('-', '/', $validated['shipping_date']),
            $collectAmounts
        );

        $filename = 'yamato_b2_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }
}
