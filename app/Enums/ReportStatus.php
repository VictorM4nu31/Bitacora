<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Finalized => 'Finalizado',
        };
    }
}
