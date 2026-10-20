<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FaxOrderFormRow;
use App\Services\FaxOrderFormPdf;
use Illuminate\Http\Response;

class FaxOrderFormPdfController extends Controller
{
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
}
