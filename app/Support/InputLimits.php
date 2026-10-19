<?php

namespace App\Support;

/** 入力項目の文字数上限。DBのカラム長を超えないように管理する。 */
final class InputLimits
{
    public const SHORT_TEXT = 255;

    public const POSTAL_CODE = 8;

    public const PHONE = 20;

    public const TRACKING_NUMBER = 20;

    public const CUSTOMER_NOTE = 1000;
}
