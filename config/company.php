<?php

return [
    // 店舗名
    'shop_name' => '髙桑商店',

    // 運営会社
    'company_name' => '有限会社 髙桑商店',
    'company_name_ari' => '（有）髙桑商店',

    // 責任者
    'company_manager' => '髙桑 亜紀',

    // 所在地
    'postal_code' => '030-0862',
    'address' => '青森県青森市古川1丁目11番地16号', // 店舗住所
    'address_sub' => '青森県青森市古川1丁目2番地5号', // 事務所
    'facility_name' => '株式会社青森魚菜センター',
    'facility_name_in' => '株式会社青森魚菜センター内',

    // 連絡先
    'phone' => '017-722-6138',
    'fax' => '017-777-0320',
    'email' => 'takakuwa@silk.plala.or.jp',
    'site_url' => env('SHOP_URL', env('APP_URL')),

    // ロゴ
    'logo' => 'images/logo.svg',

    // 振り込み先情報
    'bank' => [
        'name' => '青森みちのく銀行',
        'kana' => 'アオモリミチノクギンコウ',
        'branch' => '青森支店',
        'account_type' => '普通',
        'account_number' => '2699576',
        'account_holder' => '有)高桑商店',
    ],
];
