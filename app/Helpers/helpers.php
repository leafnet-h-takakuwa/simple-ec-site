<?php

/**
 * 共通ヘルパー
 * composer.jsonのautoloadで読み込み
 */

// 円の表示ヘルパー
// マイナスの場合に適切な位置に調整
if (!function_exists('formatYen')) {
    function formatYen(int $amount): string
    {
        return ($amount < 0 ? '-' : '')
            . '¥'
            . number_format(abs($amount));
    }
}
