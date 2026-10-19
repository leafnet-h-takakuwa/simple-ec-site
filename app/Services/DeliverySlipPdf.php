<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use TCPDF;

class DeliverySlipPdf
{
    public function generate(Order $order): string
    {
        $order->loadMissing(['orderItems', 'taxSummaries']);

        $isCod = $order->payment_method === PaymentMethod::CASH_ON_DELIVERY;

        $productTotal = (int) $order->total_amount;
        $shippingFee = (int) $order->shipping_fee;
        $codFee = $isCod ? (int) $order->cod_fee : 0;
        $grandTotal = $productTotal + $shippingFee + $codFee;

        if (!defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', storage_path('fonts/tcpdf') . DIRECTORY_SEPARATOR);
        }

        // PDF初期化
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('納品書');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 12, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // フォント
        $pdf->SetFont('ipaexg', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);

        // --------------------------------------------------
        // 共通レイアウト設定（単位：mm）
        // --------------------------------------------------

        $tableX = 15;
        $tableWidth = 180;

        // 商品名 / 単価 / 数量 / 小計
        $widths = [78, 34, 34, 34];

        $priceX = $tableX + $widths[0];
        $quantityX = $priceX + $widths[1];
        $subtotalX = $quantityX + $widths[2];
        $tableRight = $tableX + $tableWidth;

        // 金額内訳は「数量」「小計」と同じ列境界
        $summaryX = $quantityX;
        $summaryLabelWidth = $widths[2];
        $summaryAmountWidth = $widths[3];

        // --------------------------------------------------
        // 注文日・タイトル
        // --------------------------------------------------

        $pdf->SetFontSize(9);
        $pdf->SetXY(15, 12);
        $pdf->Cell(
            180,
            5,
            '注文日：' . $order->created_at->format('Y/m/d'),
            0,
            1,
            'R'
        );

        $pdf->SetFontSize(20);
        $pdf->SetXY(15, 21);
        $pdf->Cell(180, 12, '納品書', 0, 1, 'C');

        // タイトルの下線
        $pdf->Line(94, 32, 116, 32);

        $pdf->SetFontSize(9);
        $pdf->SetXY(15, 43);
        $pdf->Cell(
            180,
            6,
            'この度は、高桑商店の商品をお買い上げいただき、誠にありがとうございます。',
            0,
            1
        );

        // --------------------------------------------------
        // お届け先・請求先・発送元
        // --------------------------------------------------

        $addressY = 60;
        $addressWidth = 58;
        $addressGap = 3;

        $shipX = 15;
        $orderX = $shipX + $addressWidth + $addressGap;
        $companyX = $orderX + $addressWidth + $addressGap;

        $this->drawAddress(
            $pdf,
            $shipX,
            $addressY,
            $addressWidth,
            'お届け先：',
            $order->shipping_name . ' 様',
            [
                '〒' . $order->shipping_postal_code,
                $order->shipping_address,
                'Tel: ' . $order->shipping_phone,
            ]
        );

        $ordererLines = [
            '〒' . $order->orderer_postal_code,
            $order->orderer_address,
            'Tel: ' . $order->orderer_phone,
        ];

        if ($order->orderer_email) {
            $ordererLines[] = 'Mail: ' . $order->orderer_email;
        }

        $this->drawAddress(
            $pdf,
            $orderX,
            $addressY,
            $addressWidth,
            '請求先：',
            $order->orderer_name . ' 様',
            $ordererLines
        );

        $this->drawAddress(
            $pdf,
            $companyX,
            $addressY,
            $addressWidth,
            '発送元：',
            config('company.shop_name'),
            [
                config('company.company_name'),
                '〒' . config('company.postal_code'),
                config('company.address'),
                'Tel: ' . config('company.phone'),
                'Mail: ' . config('company.email'),
            ]
        );

        // --------------------------------------------------
        // 購入金額・決済方法
        // --------------------------------------------------

        $pdf->SetFontSize(13);
        $pdf->SetXY(15, 100);
        $pdf->Cell(
            180,
            7,
            '購入金額：￥' . number_format($grandTotal),
            0,
            1
        );

        $pdf->SetFontSize(9);
        $pdf->SetXY(15, 107);
        $pdf->Cell(
            180,
            5,
            '決済方法：' . $order->payment_method->label(),
            0,
            1
        );

        // --------------------------------------------------
        // 商品明細
        // --------------------------------------------------

        $itemsY = 118;
        $rowHeight = 6.5;
        $headerHeight = 7;

        $pdf->SetFontSize(9);

        $headers = ['商品名', '単価', '数量', '小計(税込)'];
        $x = $tableX;

        foreach ($headers as $index => $header) {
            $pdf->SetXY($x, $itemsY);
            $pdf->Cell(
                $widths[$index],
                $headerHeight,
                $header,
                1,
                0,
                'C'
            );

            $x += $widths[$index];
        }

        $currentY = $itemsY + $headerHeight;
        $itemCount = 0;
        $hasReducedTaxItem = false;

        foreach ($order->orderItems as $item) {
            $itemCount++;

            $name = $item->product_name;

            if ((int) $item->tax_rate === 8) {
                $name .= ' ※';
                $hasReducedTaxItem = true;
            }

            $values = [
                $name,
                '￥' . number_format($item->price),
                number_format($item->quantity),
                '￥' . number_format($item->subtotal),
            ];

            $aligns = ['L', 'R', 'C', 'R'];

            $this->drawTableRow(
                $pdf,
                $tableX,
                $currentY,
                $widths,
                $values,
                $aligns,
                $rowHeight
            );

            $currentY += $rowHeight;
        }

        // 10行に満たない場合は空行を追加
        for ($i = $itemCount; $i < 10; $i++) {
            $this->drawTableRow(
                $pdf,
                $tableX,
                $currentY,
                $widths,
                ['', '', '', ''],
                ['L', 'R', 'C', 'R'],
                $rowHeight
            );

            $currentY += $rowHeight;
        }

        if ($hasReducedTaxItem) {
            $pdf->SetFontSize(8);
            $pdf->SetXY($tableX, $currentY + 1);
            $pdf->Cell(
                $tableWidth,
                5,
                '(※は軽減税率対象商品)',
                0,
                1
            );
        }

        // --------------------------------------------------
        // 金額内訳
        // --------------------------------------------------

        $summaryY = $currentY + 11;
        $summaryRowHeight = 6.5;

        $summaryRows = [
            ['商品合計(税込)', $productTotal],
        ];

        if ($isCod) {
            $summaryRows[] = ['代引手数料(税込)', $codFee];
        }

        $summaryRows[] = ['送料(税込)', $shippingFee];
        $summaryRows[] = ['お支払合計金額', $grandTotal];

        $pdf->SetFontSize(8);

        foreach ($summaryRows as $index => [$label, $amount]) {
            $y = $summaryY + $index * $summaryRowHeight;

            $this->drawTableRow(
                $pdf,
                $summaryX,
                $y,
                [$summaryLabelWidth, $summaryAmountWidth],
                [$label, '￥' . number_format($amount)],
                ['R', 'R'],
                $summaryRowHeight
            );
        }

        // --------------------------------------------------
        // 消費税内訳
        // --------------------------------------------------

        $taxY = $summaryY
            + count($summaryRows) * $summaryRowHeight
            + 5;

        foreach ($order->taxSummaries->sortBy('tax_rate')->values() as $index => $summary) {
            $y = $taxY + $index * $summaryRowHeight;

            $this->drawTableRow(
                $pdf,
                $summaryX,
                $y,
                [$summaryLabelWidth, $summaryAmountWidth],
                [
                    $summary->tax_rate . '%消費税',
                    '￥' . number_format($summary->tax_amount),
                ],
                ['R', 'R'],
                $summaryRowHeight
            );
        }

        // ロゴ
        $logoPath = public_path('images/logo.png');

        if (is_file($logoPath)) {
            $logoWidth = 70; // mm
            $logoX = $tableX + 10;
            $logoY = $summaryY + 20;

            $pdf->Image(
                $logoPath,
                $logoX,
                $logoY,
                $logoWidth,
                0,
                'PNG'
            );
        }

        return $pdf->Output('', 'S');
    }

    /**
     * 住所ブロック
     */
    private function drawAddress(
        TCPDF $pdf,
        float $x,
        float $y,
        float $width,
        string $heading,
        string $name,
        array $lines
    ): void {
        $pdf->SetFontSize(9);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 5, $heading, 0, 1);

        $pdf->SetFontSize(11);
        $pdf->SetXY($x, $y + 5);
        $pdf->Cell($width, 6, $name, 0, 1);

        $pdf->SetFontSize(8);
        $currentY = $y + 15;

        foreach ($lines as $line) {
            $pdf->SetXY($x, $currentY);
            $pdf->MultiCell(
                $width,
                4,
                (string) $line,
                0,
                'L',
                false,
                1,
                $x,
                $currentY,
                true,
                0,
                false,
                true,
                4,
                'M'
            );

            $currentY = $pdf->GetY();
        }
    }

    /**
     * 枠線付きの表1行を描画
     */
    private function drawTableRow(
        TCPDF $pdf,
        float $x,
        float $y,
        array $widths,
        array $values,
        array $aligns,
        float $height
    ): void {
        foreach ($widths as $index => $width) {
            $pdf->SetXY($x, $y);
            $pdf->Cell(
                $width,
                $height,
                (string) $values[$index],
                1,
                0,
                $aligns[$index]
            );

            $x += $width;
        }
    }
}
