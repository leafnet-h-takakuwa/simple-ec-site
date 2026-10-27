<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderShipping;
use Illuminate\Http\Request;
use App\Enums\PaymentMethod;
use App\Services\B2CloudCsvService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\B2CloudImportService;
use Illuminate\Support\Facades\DB;

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

    /**
     * B2クラウド発行結果CSVの取り込み画面
     */
    public function import()
    {
        return view('admin.b2-cloud.import');
    }

    /**
     * 発行結果CSVの解析・プレビュー
     */
    public function previewImport(Request $request, B2CloudImportService $importService)
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $results = $importService->compare(
            $importService->parse($validated['csv_file'])
        );

        // 更新に必要な情報だけセッションに保存
        $importRows = collect($results)
            ->filter(fn($result) => $result['error'] === null)
            ->map(fn($result) => [
                'order_id' => $result['order_id'],
                'shipping_id' => $result['shipping_id'],
                'tracking_number' => $result['tracking_number'],
            ])
            ->values()
            ->all();

        $request->session()->put('b2_import_rows', $importRows);

        return view('admin.b2-cloud.import-preview', compact('results'));
    }

    public function executeImport(Request $request)
    {
        $rows = $request->session()->get('b2_import_rows');

        if (!is_array($rows) || count($rows) === 0) {
            return redirect()
                ->route('admin.b2-cloud.import')
                ->with('error', '取り込み対象のデータがありません。');
        }

        // 同じ個口が複数行に含まれていないか確認
        $shippingIds = array_column($rows, 'shipping_id');

        if (count($shippingIds) !== count(array_unique($shippingIds))) {
            return back()->with('error', '同じ個口がCSV内に重複しています。');
        }

        $updated = DB::transaction(function () use ($rows) {
            $count = 0;

            foreach ($rows as $row) {
                $shipping = OrderShipping::whereKey($row['shipping_id'])
                    ->lockForUpdate()
                    ->first();

                // 注文IDと個口IDが一致することを再確認
                if (!$shipping || (int) $shipping->order_id !== (int) $row['order_id']) {
                    continue;
                }

                // 同じ送り状番号なら更新しない
                if ($shipping->tracking_number === $row['tracking_number']) {
                    continue;
                }

                $shipping->tracking_number = $row['tracking_number'];
                $shipping->save();

                $count++;
            }

            return $count;
        });

        $request->session()->forget('b2_import_rows');

        return redirect()
            ->route('admin.b2-cloud.import')
            ->with('success', "送り状番号を{$updated}件登録・更新しました。");
    }
}
