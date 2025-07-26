<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    public function preview()
    {
        return response()->json([
            'url' => asset('hls/preview.m3u8'),
        ]);
    }
}
