<?php

namespace App\Enums;

enum EquipmentType: string
{
    case AirConditioning = 'air_conditioning';
    case Refrigeration = 'refrigeration';
    case Electrical = 'electrical';
    case Other = 'other';

    /**
     * Human-readable label for the equipment type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AirConditioning => 'Aire acondicionado',
            self::Refrigeration => 'Refrigeración',
            self::Electrical => 'Eléctrico',
            self::Other => 'Otro',
        };
    }
}
