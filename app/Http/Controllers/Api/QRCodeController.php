<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\PhotoSession;

class QRCodeController extends Controller
{
    public function generate(string $session_code)
    {
        $session = PhotoSession::where('session_code', $session_code)->firstOrFail();

        if (!$session->download_slug) {
            return response('Download slug missing', 400);
        }

        $downloadUrl = route('download.photo', $session->download_slug);

        // Hapus mergeString, ini yang menyebabkan imagick error
        $qr = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($downloadUrl);

        return response($qr)->header('Content-Type', 'image/png');
    }
}
