<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaxOrderFormRow;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FaxOrderFormController extends Controller
{
    /**
     * FAX注文書の編集画面
     */
    public function index()
    {
        // 現在の掲載設定
        $rows = FaxOrderFormRow::with('product')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('column');

        // 商品選択用（非公開・削除済み商品は除外）
        $products = Product::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('admin.fax-order-form.index', [
            'rows' => $rows,
            'products' => $products
        ]);
    }

    /**
     * FAX注文書の一括保存
     */
    public function update(Request $request)
    {
        $request->merge([
            'rows' => $request->input('rows', []),
        ]);

        $validated = $request->validate([
            'rows' => ['present', 'array'],

            'rows.*.column' => [
                'required',
                Rule::in(['left', 'center', 'right']),
            ],
            'rows.*.type' => [
                'required',
                Rule::in(['product', 'message']),
            ],
            'rows.*.product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'rows.*.display_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'rows.*.message' => [
                'nullable',
                'string',
                'max:255',
                'not_regex:/[\r\n]/',
            ],
        ]);

        // 各列の商品・メッセージの合計を25行以内に制限
        $columnNames = [
            'left' => '左列',
            'center' => '中央列',
            'right' => '右列',
        ];

        foreach ($columnNames as $column => $name) {
            $count = collect($validated['rows'])
                ->where('column', $column)
                ->count();

            if ($count > 25) {
                throw ValidationException::withMessages([
                    'rows' => "1列の商品とメッセージは合計25件以内にしてください。",
                ]);
            }
        }

        // 行の種類に応じた入力チェック
        foreach ($validated['rows'] as $index => $row) {
            if ($row['type'] === 'product' && empty($row['product_id'])) {
                throw ValidationException::withMessages([
                    "rows.$index.product_id" => '商品を選択してください。',
                ]);
            }

            if ($row['type'] === 'message' && blank($row['message'] ?? null)) {
                throw ValidationException::withMessages([
                    "rows.$index.message" => 'メッセージを入力してください。',
                ]);
            }
        }

        DB::transaction(function () use ($validated) {
            // 現在の設定を全削除
            FaxOrderFormRow::query()->delete();

            // 列・種類ごとに表示順を振り直して登録
            $sortOrders = [];

            foreach ($validated['rows'] as $row) {
                $key = $row['column'] . ':' . $row['type'];

                $sortOrders[$key] = ($sortOrders[$key] ?? 0) + 1;

                FaxOrderFormRow::create([
                    'column' => $row['column'],
                    'sort_order' => $sortOrders[$key],
                    'type' => $row['type'],
                    'product_id' => $row['type'] === 'product'
                        ? $row['product_id']
                        : null,
                    'display_name' => $row['type'] === 'product'
                        ? ($row['display_name'] ?? null)
                        : null,
                    'message' => $row['type'] === 'message'
                        ? $row['message']
                        : null,
                ]);
            }
        });

        return redirect()
            ->route('admin.fax-order-form.index')
            ->with('success', 'FAX注文書の設定を保存しました。');
    }
}
