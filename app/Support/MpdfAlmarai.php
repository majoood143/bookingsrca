<?php

namespace App\Support;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class MpdfAlmarai
{
    // mPDF replaces (rather than merges) 'fontDir'/'fontdata' when set, so the
    // defaults must be merged in manually to keep the built-in fonts available.
    public static function config(): array
    {
        $fontDirs = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        return [
            'fontDir' => array_merge($fontDirs, [public_path('fonts/almarai')]),
            'fontdata' => $fontData + [
                'almarai' => [
                    'R'          => 'Almarai-Regular.ttf',
                    'B'          => 'Almarai-Bold.ttf',
                    // Enable OpenType Layout so mPDF applies Arabic character
                    // shaping (contextual glyph forms / letter joining).
                    // 0xFF enables all OTL feature tables (GSUB + GPOS).
                    'useOTL'     => 0xFF,
                    'useKashida' => 75,
                ],
            ],
        ];
    }

    // Returns mPDF instance properties needed for proper Arabic rendering.
    // Call after new Mpdf() when generating Arabic (RTL) documents.
    public static function applyArabicSettings(\Mpdf\Mpdf $mpdf): void
    {
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->baseScript       = 1;   // Arabic as primary script
        $mpdf->autoVietnamese   = false;
    }
}
