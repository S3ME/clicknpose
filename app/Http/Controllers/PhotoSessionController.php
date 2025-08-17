<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Template;
use App\Models\PhotoSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class PhotoSessionController extends Controller
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new GdDriver());
    }

    public function finish(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'photos' => 'required|array',
            'photos.*' => 'required|string',
        ]);

        $template = Template::findOrFail($data['template_id']);
        $session = $this->createSession($template->id);

        $photoPaths = $this->storePhotos($data['photos'], $session);
        $resultPath = $this->mergePhotosWithTemplate($photoPaths, $template, $session->session_code);

        $session->update([
            'final_image_path' => $resultPath,
            'download_slug' => Str::random(12),
        ]);

        // Redirect ke halaman preview berdasarkan ID atau session_code
        return response()->json([
            'session_code' => $session->session_code,
        ]);
    }

    public function preview(PhotoSession $session)
    {
        return Inertia::render('Preview', ['session' => $session]);
    }

    public function download(string $slug)
    {
        $session = PhotoSession::where('download_slug', $slug)->firstOrFail();
        return Storage::disk('public')->download($session->result_path);
    }

    // ===== Helper Methods =====

    private function createSession(int $templateId): PhotoSession
    {
        return PhotoSession::create([
            'template_id' => $templateId,
            'session_code' => strtoupper(Str::random(6)),
            'status' => 'completed',
        ]);
    }

    private function storePhotos(array $base64Photos, PhotoSession $session): array
    {
        $paths = [];

        foreach ($base64Photos as $index => $base64) {
            $stream = $this->decodeBase64($base64, $index);
            $image = $this->imageManager->read($stream)->toPng();
            fclose($stream); // jangan lupa tutup stream

            $filename = "{$session->session_code}_slot_" . ($index + 1) . '.png';
            $storagePath = "images/photos/{$filename}";

            Storage::disk('public')->put($storagePath, (string) $image);

            Photo::create([
                'session_id' => $session->id,
                'sequence' => $index + 1,
                'file_path' => $storagePath,
                'retaken' => false,
            ]);

            $paths[] = storage_path("app/public/{$storagePath}");
        }

        return $paths;
    }

    private function decodeBase64(string $base64, int $index)
    {
        if (str_starts_with($base64, 'data:image')) {
            [, $base64] = explode(',', $base64);
        }

        if (empty($base64)) {
            throw new \Exception("Base64 kosong pada index {$index}");
        }

        $decoded = base64_decode($base64, true); // true: strict mode

        if ($decoded === false) {
            throw new \Exception("Gagal decode base64 pada index {$index}");
        }

        // Convert to stream (Intervention v3 prefers streams or paths)
        $stream = tmpfile();
        fwrite($stream, $decoded);
        rewind($stream);

        file_put_contents(storage_path("logs/photo_debug_{$index}.png"), $decoded);

        return $stream;
    }

    private function mergePhotosWithTemplate(array $photoPaths, Template $template, string $sessionCode): string
    {
        $layout = json_decode($template->layout_json, true);

        if (!$layout || !is_array($layout)) {
            throw new \Exception("layout_json kosong atau tidak valid.");
        }

        $templatePath = storage_path('app/public/' . $template->image_path);

        if (!file_exists($templatePath)) {
            throw new \Exception("Template file tidak ditemukan di {$templatePath}");
        }

        try {
            // Baca template untuk mendapatkan dimensi asli
            $templateImage = $this->imageManager->read($templatePath);
            $templateWidth = $templateImage->width();
            $templateHeight = $templateImage->height();
        } catch (\Exception $e) {
            throw new \Exception("Gagal membaca template image: " . $e->getMessage());
        }

        // Buat canvas kosong dengan transparansi
        $canvas = $this->imageManager->create($templateWidth, $templateHeight)
            ->fill('rgba(0,0,0,0)'); // transparan

        // Tempelkan foto-foto ke canvas (di bawah)
        foreach ($layout as $index => $area) {
            if (!isset($photoPaths[$index])) {
                continue;
            }

            try {
                // $photo = $this->imageManager->read($photoPaths[$index])
                //     ->resize($area['width'], $area['height']);
                $photo = $this->imageManager->read($photoPaths[$index])
                    ->cover($area['width'], $area['height']);

                $canvas->place($photo, 'top-left', $area['x'], $area['y']);
            } catch (\Exception $e) {
                throw new \Exception("Gagal merge photo ke-{$index}: " . $e->getMessage());
            }
        }

        // Tempelkan template PNG di atasnya
        $canvas->place($templateImage, 'top-left', 0, 0);

        // Simpan hasil
        $resultPath = "images/result/{$sessionCode}.png";

        try {
            Storage::disk('public')->put($resultPath, (string) $canvas->toPng());
        } catch (\Exception $e) {
            throw new \Exception("Gagal simpan hasil: " . $e->getMessage());
        }

        return $resultPath;
    }
}
