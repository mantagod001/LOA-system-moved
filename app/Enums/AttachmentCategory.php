<?php

namespace App\Enums;

enum AttachmentCategory: string
{
    case Medical = 'medical';
    case ValidId = 'valid_id';
    case ParentConsent = 'parent_consent';
    case BarangayCertificate = 'barangay_certificate';
    case Affidavit = 'affidavit';
    case Transcript = 'transcript';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Medical => 'Medical certificate',
            self::ValidId => 'Valid ID',
            self::ParentConsent => 'Parent / guardian consent',
            self::BarangayCertificate => 'Barangay certificate',
            self::Affidavit => 'Affidavit',
            self::Transcript => 'Transcript / grades',
            self::Other => 'Other supporting document',
        };
    }
}
