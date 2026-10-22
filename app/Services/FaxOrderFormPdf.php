<?php

namespace App\Services;

use TCPDF;

class FaxOrderFormPdf
{
    private const COLUMNS = ['left', 'center', 'right'];
    private const MAX_PRODUCTS = 25;
    private const TABLE_Y = 33.0;
    private const HEADER_H = 5.5;
    private const ROW_H = 4.8;

    /**
     * @param array<string, array{products: array<int, array{name:string, volume:string, price:int}>, messages: array<int, string>}> $columns
     */
    public function render(array $columns, ?string $logoPath = null, bool $preview = false): string
    {
        if (!defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', storage_path('fonts/tcpdf') . DIRECTORY_SEPARATOR);
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('髙桑商店');
        $pdf->SetTitle('【高桑商店】FAX注文書');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        $pdf->SetFont('ipaexg', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);

        $pdf->SetFontSize(7.5);
        $pdf->SetXY(11, 8);
        $pdf->Cell(189, 5, 'こちらをプリントアウトしてご記入後、FAX送信願います。', 0, 1, 'R');
        $pdf->SetFontSize(16);
        $pdf->SetXY(11, 14);
        $pdf->Cell(189, 9, 'FAX番号 ' . config('company.fax'), 0, 1, 'C');
        $pdf->SetFontSize(14);
        $pdf->SetXY(11, 25);
        $pdf->Cell(100, 7, 'FAX御注文書', 0, 0, 'L');
        $pdf->SetFontSize(7.5);
        $pdf->SetXY(130, 26.5);
        $pdf->Cell(70, 5, '※ 表示価格は税込です。送料・各種手数料は別途かかります。', 0, 0, 'R');

        foreach (self::COLUMNS as $index => $key) {
            $this->drawProductColumn($pdf, 11 + 63 * $index, $columns[$key] ?? ['products' => [], 'messages' => []]);
        }

        $this->drawAddressBox($pdf, 11, '御注文者様氏名');
        $this->drawAddressBox($pdf, 107, 'お届け先氏名');
        $this->drawFooter($pdf, $logoPath);
        // プレビュー表示
        if ($preview) {
            $this->drawPreviewWatermark($pdf);
        }

        return $pdf->Output('', 'S');
    }

    private function drawProductColumn(TCPDF $pdf, float $x, array $column): void
    {
        $products = $column['products'] ?? [];
        $messages = $column['messages'] ?? [];

        $y = self::TABLE_Y;
        $w = 60.0;
        $bottom = $y + self::HEADER_H + self::MAX_PRODUCTS * self::ROW_H;
        $widths = [25.2, 11.0, 12.0, 11.8];
        $pdf->SetLineWidth(0.35);
        $pdf->Rect($x, $y, $w, $bottom - $y);
        $pdf->Line($x, $y + self::HEADER_H, $x + $w, $y + self::HEADER_H);
        $pdf->SetFontSize(6.8);
        $labels = ['商 品 名', '内容量', '価格(円)', '数量'];
        $cellX = $x;
        foreach ($labels as $i => $label) {
            $pdf->SetXY($cellX, $y);
            $pdf->Cell($widths[$i], self::HEADER_H, $label, 'R', 0, 'C');
            $cellX += $widths[$i];
        }

        foreach ($products as $index => $product) {
            $rowY = $y + self::HEADER_H + $index * self::ROW_H;
            $pdf->SetLineWidth(0.16);
            $pdf->Line($x, $rowY + self::ROW_H, $x + $w, $rowY + self::ROW_H);
            $lineX = $x;
            foreach (array_slice($widths, 0, 3) as $width) {
                $lineX += $width;
                $pdf->Line($lineX, $rowY, $lineX, $rowY + self::ROW_H);
            }
            $values = [
                (string) $product['name'],
                (string) $product['volume'],
                number_format((int) $product['price']),
                '',
            ];
            $cellX = $x;
            foreach ($values as $i => $value) {
                $align = $i === 0 ? 'L' : ($i === 2 ? 'R' : 'C');
                $textX = $cellX + ($i === 0 ? 0.8 : 0.4);
                $textW = $widths[$i] - ($i === 0 ? 1.6 : 0.8);
                $fontSize = 7.0;
                $pdf->SetFontSize($fontSize);
                while ($fontSize > 5.0 && $pdf->GetStringWidth($value) > $textW) {
                    $fontSize -= 0.25;
                    $pdf->SetFontSize($fontSize);
                }
                $pdf->SetXY($textX, $rowY + 0.55);
                $pdf->Cell($textW, 3.7, $value, 0, 0, $align, false, '', 0, false, 'T', 'M');
                $cellX += $widths[$i];
            }
        }

        // 商品の下に残った余白
        $blankTop = $y + self::HEADER_H + count($products) * self::ROW_H;

        // 空メッセージを除外
        $messages = array_values(array_filter(
            array_map('trim', $messages),
            fn($message) => $message !== ''
        ));

        if ($messages) {
            // メッセージ1件 = 商品1行分の高さ
            $lineH = self::ROW_H;

            // メッセージ全体の高さ
            $needed = count($messages) * $lineH;

            // 余白の中央に配置
            $startY = $blankTop + ($bottom - $blankTop - $needed) / 2;

            $pdf->SetFontSize(7.2);

            foreach ($messages as $i => $message) {
                $pdf->SetXY($x + 1, $startY + $i * $lineH);
                $pdf->Cell($w - 2, $lineH, $message, 0, 0, 'C');
            }
        }
    }

    private function drawAddressBox(TCPDF $pdf, float $x, string $nameLabel): void
    {
        $y = 165;
        $w = 93;
        $h = 72;
        $pdf->SetLineWidth(0.35);
        $pdf->Rect($x, $y, $w, $h);
        $pdf->Line($x, $y + 5.5, $x + $w, $y + 5.5);
        $pdf->Line($x, $y + 24, $x + $w, $y + 24);
        $pdf->Line($x, $y + 29.5, $x + $w, $y + 29.5);
        $pdf->SetFontSize(8);
        $pdf->SetXY($x + 1.5, $y + 0.5);
        $pdf->Cell($w - 3, 4.5, $nameLabel);
        $pdf->SetFontSize(13);
        $pdf->SetXY($x + $w - 16, $y + 15);
        $pdf->Cell(13, 6, '様', 0, 0, 'R');
        $pdf->SetFontSize(8);
        $pdf->SetXY($x + 1.5, $y + 24.5);
        $pdf->Cell($w - 3, 4.5, 'ご住所・電話番号');
        $pdf->SetFontSize(9);
        $pdf->SetXY($x + 2, $y + 31);
        $pdf->Cell(10, 5, '〒');
        $pdf->SetXY($x + 10, $y + 61);
        $pdf->Cell($w - 20, 5, 'TEL  （               ）              ―', 0, 0, 'C');
    }

    private function drawFooter(TCPDF $pdf, ?string $logoPath): void
    {
        if ($logoPath && is_file($logoPath)) {
            $pdf->Image($logoPath, 15, 244, 81, 0, '', '', '', false, 300);
        }
        $pdf->SetFontSize(15);
        $pdf->SetXY(103, 250);
        $pdf->Cell(95, 9, config('company.company_name'), 0, 1, 'C');
        $pdf->SetFontSize(8);
        $pdf->SetXY(106, 261);
        $pdf->MultiCell(92, 4.2, "〒" . config('company.postal_code') . " " . config('company.address') . "\n（" . config('company.facility_name') . "内 ※ 火曜定休）\nTEL: " . config('company.phone') . " FAX:" . config('company.fax'), 0, 'C');
        $pdf->SetFontSize(7.5);
        $pdf->SetXY(100, 279);
        $pdf->Cell(95, 5, now()->format('Y年n月j日') . ' 発行', 0, 0, 'R');
        $pdf->SetFontSize(6);
        $pdf->SetXY(107, 283);
        $pdf->Cell(95, 5, '※ 価格は変更される場合があります。', 0, 0, 'R');
    }

    private function drawPreviewWatermark(TCPDF $pdf): void
    {
        $pdf->StartTransform();

        // ページ中央を基準に斜めに回転
        $pdf->Rotate(35, 105, 148);

        $pdf->SetAlpha(0.30);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetFont('ipaexg', 'B', 52);

        $pdf->SetXY(10, 132);
        $pdf->Cell(190, 30, 'プレビュー中', 0, 0, 'C');

        $pdf->StopTransform();

        // 描画設定を元に戻す
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);
    }
}
