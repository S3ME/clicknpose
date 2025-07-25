<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoSession;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function show(string $slug): StreamedResponse
    {
        $session = PhotoSession::where('download_slug', $slug)->firstOrFail();

        $path = storage_path('app/public/' . $session->final_image_path);

        return response()->download($path, 'photo.png');
    }
}

