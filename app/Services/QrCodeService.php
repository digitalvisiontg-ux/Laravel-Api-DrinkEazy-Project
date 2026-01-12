<?php

namespace App\Services;

use App\Models\Table;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateForTable(Table $table): string
    {
        // CONTENU DU QR : TOKEN UNIQUEMENT
        $token = $table->token;

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $token, // ✅ CORRECT
            encoding: new Encoding('UTF-8'),
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        $path = "qrcodes/table_{$table->id}.png";
        Storage::disk('public')->put($path, $result->getString());

        return $path;
    }
}

