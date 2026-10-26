<?php

return [
    // CSV出力時の文字コード
    'encoding' => env('B2_CSV_ENCODING', 'SJIS-win'),

    // ご依頼主情報
    'sender' => [
        'name' => env('B2_SENDER_NAME', ''),               // No.25 ご依頼主名
        'phone' => env('B2_SENDER_PHONE', ''),             // No.20 ご依頼主電話番号
        'postal_code' => env('B2_SENDER_POSTAL_CODE', ''), // No.22 ご依頼主郵便番号
        'address' => env('B2_SENDER_ADDRESS', ''),         // No.23 ご依頼主住所
    ],

    // 運賃請求先情報
    'billing' => [
        'customer_code' => env('B2_BILLING_CUSTOMER_CODE', ''),                 // No.40 ご請求先顧客コード
        'classification_code' => env('B2_BILLING_CLASSIFICATION_CODE', ''),     // No.41 ご請求先分類コード
        'freight_management_number' => env('B2_FREIGHT_MANAGEMENT_NUMBER', ''), // No.42 運賃管理番号
    ],
];
