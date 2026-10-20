<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FaxOrderFormRow;
use App\Services\FaxOrderFormPdf;
use Illuminate\Http\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FaxOrderFormPdfController extends Controller
{

    // 顧客向けの注文書出力(DBに保存してあるものから出力)
    public function show(FaxOrderFormPdf $pdfService): Response
    {
        $columns = array_fill_keys(['left', 'center', 'right'], [
            'products' => [],
            'messages' => [],
        ]);

        $rows = FaxOrderFormRow::with('product')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lastUpdatedAt = $rows
            ->pluck('product')
            ->filter()
            ->max('updated_at');

        foreach ($rows as $row) {
            if (!array_key_exists($row->column, $columns)) {
                continue;
            }

            if ($row->type === 'message') {
                if (filled($row->message)) {
                    $columns[$row->column]['messages'][] = $row->message;
                }
                continue;
            }

            $product = $row->product;
            if (!$product || !$product->is_active) {
                continue;
            }

            $columns[$row->column]['products'][] = [
                'name' => $row->display_name ?: $product->name,
                'volume' => (string) $product->volume,
                'price' => (int) $product->price,
            ];
        }

        $logo = public_path('images/logo.jpg');
        $binary = $pdfService->render($columns, $logo);
        $filename = '【高桑商店】FAX注文書_' . now()->format('Ymd') . '.pdf';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    // 管理者向けの注文書出力(フォームに入力してあるものから出力)
    public function preview(Request $request, FaxOrderFormPdf $pdfService): Response
    {
        $validated = $request->validate([
            'rows' => ['present', 'array'],
            'rows.*.column' => ['required', 'in:left,center,right'],
            'rows.*.type' => ['required', 'in:product,message'],
            'rows.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'rows.*.display_name' => ['nullable', 'string', 'max:100'],
            'rows.*.message' => [
                'nullable',
                'string',
                'max:255',
                'not_regex:/[\r\n]/',
            ],
        ]);

        $rows = collect($validated['rows']);

        foreach (['left', 'center', 'right'] as $column) {
            if ($rows->where('column', $column)->count() > 25) {
                throw ValidationException::withMessages([
                    'rows' => "1列の商品とメッセージは合計25件以内にしてください。",
                ]);
            }
        }

        $productIds = $rows
            ->where('type', 'product')
            ->pluck('product_id')
            ->filter()
            ->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $columns = array_fill_keys(['left', 'center', 'right'], [
            'products' => [],
            'messages' => [],
        ]);

        foreach ($rows as $row) {
            $column = $row['column'];

            if ($row['type'] === 'message') {
                if (filled($row['message'] ?? null)) {
                    $columns[$column]['messages'][] = $row['message'];
                }

                continue;
            }

            $product = $products->get($row['product_id'] ?? null);

            // 公開PDFと同じく、非公開・削除済みの商品は表示しない。
            if (!$product || !$product->is_active) {
                continue;
            }

            $columns[$column]['products'][] = [
                'name' => $row['display_name'] ?: $product->name,
                'volume' => (string) $product->volume,
                'price' => (int) $product->price,
            ];
        }

        $binary = $pdfService->render(
            $columns,
            public_path('images/logo.jpg'),
            true
        );

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fax-order-form-preview.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
