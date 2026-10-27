<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use App\Models\OrderShipping;

class B2CloudImportService
{
    use B2CloudImportComparison;

    public function parse(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        // B2クラウドのShift_JISとUTF-8に対応
        $encoding = mb_detect_encoding($content, ['UTF-8', 'SJIS-win'], true);

        if ($encoding === false) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSVの文字コードを判別できませんでした。',
            ]);
        }

        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $content);
        rewind($stream);

        try {
            $headers = fgetcsv($stream);

            if (!$headers) {
                throw ValidationException::withMessages([
                    'csv_file' => 'CSVが空です。',
                ]);
            }

            $required = ['お客様管理番号', '伝票番号'];

            foreach ($required as $column) {
                if (!in_array($column, $headers, true)) {
                    throw ValidationException::withMessages([
                        'csv_file' => "必要な列「{$column}」がありません。",
                    ]);
                }
            }

            $results = [];
            $line = 1;

            while (($values = fgetcsv($stream)) !== false) {
                $line++;

                // 空行は無視
                if ($values === [null]) {
                    continue;
                }

                // 末尾の空欄が省略されている場合に対応
                $values = array_pad($values, count($headers), '');

                if (count($values) !== count($headers)) {
                    $results[] = [
                        'line' => $line,
                        'error' => 'CSVの列数が一致しません。',
                    ];
                    continue;
                }

                $row = array_combine($headers, $values);

                $managementNumber = trim($row['お客様管理番号']);
                $trackingNumber = trim($row['伝票番号']);

                if (!preg_match('/^TS-(\d{8})-(\d{8})$/', $managementNumber, $matches)) {
                    $results[] = [
                        'line' => $line,
                        'error' => 'お客様管理番号の形式が不正です。',
                    ];
                    continue;
                }

                if ($trackingNumber === '') {
                    $results[] = [
                        'line' => $line,
                        'error' => '伝票番号が未入力です。',
                    ];
                    continue;
                }

                $results[] = [
                    'line' => $line,
                    'order_id' => (int) $matches[1],
                    'shipping_id' => (int) $matches[2],
                    'management_number' => $managementNumber,
                    'tracking_number' => $trackingNumber,
                    'row' => $row,
                    'error' => null,
                ];
            }

            return $results;
        } finally {
            fclose($stream);
        }
    }

    public function compare(array $results): array
    {
        foreach ($results as &$result) {
            if ($result['error'] !== null) {
                $result['status'] = 'error';
                continue;
            }

            $shipping = OrderShipping::find($result['shipping_id']);

            if (!$shipping || $shipping->order_id !== $result['order_id']) {
                $result['status'] = 'error';
                $result['error'] = '該当する注文・個口が見つかりません。';
                continue;
            }

            $result['warnings'] = $this->checkWarnings(
                $result['row'],
                $shipping
            );

            $result['current_tracking_number'] = $shipping->tracking_number;

            $result['status'] = match (true) {
                blank($shipping->tracking_number) => 'new',
                $shipping->tracking_number === $result['tracking_number'] => 'same',
                default => 'changed',
            };
        }

        unset($result);

        return $results;
    }
}
