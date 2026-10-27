<?php

return [
    // CSV出力時の文字コード
    'encoding' => env('B2_CSV_ENCODING', 'SJIS-win'),

    // 荷扱い
    'handling' => [
        'item1' => '天地無用',  // No.31 荷扱い1
        'item2' => '下積厳禁',  // No.32 荷扱い2
    ],

    // 運賃請求先情報
    'billing' => [
        'customer_code' => env('B2_BILLING_CUSTOMER_CODE', ''),                 // No.40 ご請求先顧客コード
        'classification_code' => env('B2_BILLING_CLASSIFICATION_CODE', ''),     // No.41 ご請求先分類コード
        'freight_management_number' => env('B2_FREIGHT_MANAGEMENT_NUMBER', ''), // No.42 運賃管理番号
    ],
];
