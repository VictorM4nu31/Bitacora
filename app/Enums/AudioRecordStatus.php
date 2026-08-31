<?php

namespace App\Enums;

enum AudioRecordStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Transcribed = 'transcribed';
    case Failed = 'failed';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Subido',
            self::Processing => 'Procesando',
            self::Transcribed => 'Transcrito',
            self::Failed => 'Error',
        };
    }
}
