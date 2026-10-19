<?php

namespace App\Support;

final class ContactRules
{
    /** 注文者・配送先で共通の連絡先項目。必須条件は呼び出し元で指定する。 */
    public static function forPrefix(string $prefix, array $presence = ['required']): array
    {
        return [
            $prefix.'name' => [...$presence, 'string', 'max:'.InputLimits::SHORT_TEXT],
            $prefix.'postal_code' => [...$presence, 'string', 'max:'.InputLimits::POSTAL_CODE],
            $prefix.'address' => [...$presence, 'string', 'max:'.InputLimits::SHORT_TEXT],
            $prefix.'phone' => [...$presence, 'string', 'max:'.InputLimits::PHONE],
        ];
    }
}
