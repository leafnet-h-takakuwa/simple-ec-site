<?php

namespace App\Enums;

enum CoolType: string
{
    case NORMAL = 'normal';
    case FROZEN = 'frozen';
    case REFRIGERATED = 'refrigerated';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => '常温',
            self::FROZEN => '冷凍',
            self::REFRIGERATED => '冷蔵',
        };
    }

    public function b2Code(): string
    {
        return match ($this) {
            self::NORMAL => '0',
            self::FROZEN => '1',
            self::REFRIGERATED => '2',
        };
    }
}
