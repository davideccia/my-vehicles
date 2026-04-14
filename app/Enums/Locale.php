<?php

namespace App\Enums;

enum Locale: string
{
    case ITALIAN = 'it';
    case ENGLISH = 'en';

    public function label(): string
    {
        return match ($this) {
            self::ITALIAN => '🇮🇹 Italiano',
            self::ENGLISH => '🇬🇧 English',
        };
    }
}
