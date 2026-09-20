<?php

namespace App\Enums;

enum VatTreatment: string
{
    case Standard = 'standard';
    case Reduced15 = 'reduced_15';
    case Reduced12 = 'reduced_12';
    case Exempt = 'exempt';
    case Outside = 'outside';

    public function rate(): int
    {
        return match ($this) {
            self::Standard => 25,
            self::Reduced15 => 15,
            self::Reduced12 => 12,
            self::Exempt, self::Outside => 0,
        };
    }
}
