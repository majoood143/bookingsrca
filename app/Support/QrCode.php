<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

/** A QR code for a link, as a PNG data URI (posters, venue screens, the admin share modal). */
final class QrCode
{
    public static function dataUri(string $data, int $size = 360): string
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        ))->build();

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
