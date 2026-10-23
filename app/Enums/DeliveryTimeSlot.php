<?php

namespace App\Enums;

enum DeliveryTimeSlot: string
{
    case MORNING = 'morning';
    case AFTERNOON_14_16 = '14-16';
    case EVENING_16_18 = '16-18';
    case EVENING_18_20 = '18-20';
    case NIGHT_19_21 = '19-21';

    public function label(): string
    {
        return match ($this) {
            self::MORNING => '午前中',
            self::AFTERNOON_14_16 => '14:00〜16:00',
            self::EVENING_16_18 => '16:00〜18:00',
            self::EVENING_18_20 => '18:00〜20:00',
            self::NIGHT_19_21 => '19:00〜21:00',
        };
    }
}
