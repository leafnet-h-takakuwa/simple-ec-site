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

        // 商品名 / 内容量 / 単価 / 数量 / 小計
        $widths = [88, 24, 20, 18, 30];

        $volumeX = $tableX + $widths[0];
        $priceX = $volumeX + $widths[1];
        $quantityX = $priceX + $widths[2];
        $subtotalX = $quantityX + $widths[3];
        $tableRight = $tableX + $tableWidth;

        // 金額内訳は「単価＋数量」「小計」の列境界に合わせる
        $summaryX = $priceX;
        $summaryLabelWidth = $widths[2] + $widths[3];
        $summaryAmountWidth = $widths[4];

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

        $shipEndY = $this->drawAddress(
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

        $orderEndY = $this->drawAddress(
            $pdf,
            $orderX,
            $addressY,
            $addressWidth,
            '請求先：',
            $order->orderer_name . ' 様',
            $ordererLines
        );

        $companyEndY = $this->drawAddress(
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

        // 3列のうち最も長いブロックに合わせて、後続の表示位置を調整
        $purchaseY = max(100, max($shipEndY, $orderEndY, $companyEndY) + 5);

        // --------------------------------------------------
        // 購入金額・決済方法
        // --------------------------------------------------

        $pdf->SetFontSize(13);
        $pdf->SetXY(15, $purchaseY);
        $pdf->Cell(
            180,
            7,
            '購入金額：￥' . number_format($grandTotal),
            0,
            1
        );

        $pdf->SetFontSize(9);
        $pdf->SetXY(15, $purchaseY + 7);
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

        $itemsY = $purchaseY + 18;
        $rowHeight = 6.5;
        $headerHeight = 7;

        $pdf->SetFontSize(9);

        $headers = ['商品名', '内容量', '単価', '数量', '小計(税込)'];
        $drawHeader = function (float $y) use ($pdf, $headers, $tableX, $widths, $headerHeight): void {
            $pdf->SetFontSize(9);
            $x = $tableX;
            foreach ($headers as $index => $header) {
                $pdf->SetXY($x, $y);
                $pdf->Cell($widths[$index], $headerHeight, $header, 1, 0, 'C');
                $x += $widths[$index];
            }
        };

        $drawHeader($itemsY);
        $currentY = $itemsY + $headerHeight;
        $pageBottom = 290; // A4下端297mmから7mmの余白
        $itemCount = 0;
        $hasReducedTaxItem = false;

        // 明細行が用紙に収まらないときだけ改ページ
        $ensureRowFits = function (float $height) use ($pdf, &$currentY, $pageBottom, $headerHeight, $drawHeader): void {
            if ($currentY + $height > $pageBottom) {
                $pdf->AddPage();
                $drawHeader(20);
                $currentY = 20 + $headerHeight;
            }
        };

        foreach ($order->orderItems as $item) {
            $itemCount++;
            $name = $item->product_name;

            if ((int) $item->tax_rate === 8) {
                $name .= ' ※';
                $hasReducedTaxItem = true;
            }

            $volume = $item->volume ?? '';

            // 商品名と内容量の折り返し行数に合わせて明細行の高さを決める
            $pdf->SetFontSize(9);
            $nameLines = $pdf->getNumLines($name, $widths[0] - 4);
            $volumeLines = $volume !== '' ? $pdf->getNumLines($volume, $widths[1] - 4) : 1;
            $itemRowHeight = max($rowHeight, max($nameLines, $volumeLines) * 5 + 2);

            $ensureRowFits($itemRowHeight);
            $this->drawItemRow(
                $pdf,
                $tableX,
                $currentY,
                $widths,
                [
                    $name,
                    $volume,
                    formatYen($item->price),
                    number_format($item->quantity),
                    formatYen($item->subtotal),
                ],
                $itemRowHeight
            );
            $currentY += $itemRowHeight;
        }

        // 従来どおり最低10行分を表示
        for ($i = $itemCount; $i < 10; $i++) {
            $ensureRowFits($rowHeight);
            $this->drawTableRow(
                $pdf,
                $tableX,
                $currentY,
                $widths,
                ['', '', '', '', ''],
                ['L', 'C', 'R', 'C', 'R'],
                $rowHeight
            );
            $currentY += $rowHeight;
        }

        // 注記は最終ページの明細直下に表示する
        if ($hasReducedTaxItem) {
            if ($currentY + 6 > $pageBottom) {
                $pdf->AddPage();
                $drawHeader(20);
                $currentY = 20 + $headerHeight;
            }
            $pdf->SetFontSize(8);
            $pdf->SetXY($tableX, $currentY + 1);
            $pdf->Cell($tableWidth, 5, '(※は軽減税率（8%）対象の明細)', 0, 1);
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

        // 金額内訳・税内訳が収まらない場合は、まとめて次ページへ送る
        $taxCount = $order->taxSummaries->count();
        $summaryEndY = $summaryY
            + count($summaryRows) * $summaryRowHeight
            + 5
            + $taxCount * $summaryRowHeight;

        if ($summaryEndY > $pageBottom) {
            $pdf->AddPage();
            $summaryY = 25;
        }

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
        $logoPath = public_path('images/logo.jpg');

        if (is_file($logoPath)) {
            $logoWidth = 70; // mm
            $logoX = $tableX + 10;
            $logoY = $summaryY + 15;

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
    ): float {
        $pdf->SetFontSize(9);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 5, $heading, 0, 1);

        // 宛名も長い場合は列幅の中で折り返す
        $pdf->SetFontSize(11);
        $nameY = $y + 5;
        $nameHeight = max(6, $pdf->getNumLines($name, $width - 2) * 5);
        $pdf->MultiCell(
            $width,
            $nameHeight,
            $name,
            0,
            'L',
            false,
            1,
            $x,
            $nameY,
            true,
            0,
            false,
            true,
            0,
            'T'
        );

        $pdf->SetFontSize(8);
        $currentY = max($y + 15, $nameY + $nameHeight + 4);

        foreach ($lines as $line) {
            // 住所や長いメールアドレスを折り返し、次の項目は実際の終了位置から描画
            $text = (string) $line;
            $lineHeight = max(4, $pdf->getNumLines($text, $width - 2) * 4);
            $pdf->MultiCell(
                $width,
                $lineHeight,
                $text,
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
                0,
                'T'
            );
            $currentY = max($pdf->GetY(), $currentY + $lineHeight);
        }

        return $currentY;
    }

    /**
     * 商品名・内容量を折り返し、他の列は同じ高さのセルで描画する
     */
    private function drawItemRow(
        TCPDF $pdf,
        float $x,
        float $y,
        array $widths,
        array $values,
        float $height
    ): void {
        $pdf->SetFontSize(9);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(
            $widths[0],
            $height,
            (string) $values[0],
            1,
            'L',
            false,
            0,
            $x,
            $y,
            true,
            0,
            false,
            true,
            $height,
            'M'
        );

        $x += $widths[0];

        // 内容量も長い場合はセル内で折り返す
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(
            $widths[1],
            $height,
            (string) $values[1],
            1,
            'C',
            false,
            0,
            $x,
            $y,
            true,
            0,
            false,
            true,
            $height,
            'M'
        );

        $x += $widths[1];
        foreach (['R', 'C', 'R'] as $index => $align) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($widths[$index + 2], $height, (string) $values[$index + 2], 1, 0, $align);
            $x += $widths[$index + 2];
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
