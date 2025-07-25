<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use App\Models\Template;
use Inertia\Inertia;

class TemplateController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $templates = Template::latest()->get();

        return Inertia::render('templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('templates/Create', []);
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        // 1. Validating Input
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'required|image|mimes:png|max:4096',
        ]);

        // 2. Saving Template
        $file     = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('templates', $filename, 'public');
        $fullPath = storage_path('app/public/' . $path);

        // 3. Remove if Exists
        if (!File::exists(public_path('templates'))) {
            File::makeDirectory(public_path('templates'), 0755, true);
        }

        // 4. Validating Image
        $image = @imagecreatefrompng($fullPath);
        if (!$image) {
            return back()->with('error', 'Gagal memproses gambar. Pastikan file PNG valid.');
        }
        imagesavealpha($image, true);

        // 5. Detect Transparent Areas
        $layoutJson = $this->detectTransparentAreas($image);

        // 6. Saving to Database
        Template::create([
            'name'         => $request->name,
            'image_path'   => $path,
            'layout_json'  => json_encode($layoutJson),
        ]);

        return redirect()->route('templates.index')->with('success', 'Template berhasil ditambahkan.');
    }

    /**
     * Detect transparent area in the image and return layout JSON.
     */
    private function detectTransparentAreas($image): array
    {
        $width   = imagesx($image);
        $height  = imagesy($image);
        $visited = array_fill(0, $height, array_fill(0, $width, false));
        $boxes   = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($visited[$y][$x]) continue;

                $rgba  = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;

                if ($alpha >= 127) {
                    $box = $this->floodFill($image, $x, $y, $visited);
                    if ($box) $boxes[] = $box;
                }
            }
        }

        return $boxes;
    }

    /**
     * Flood fill algorithm to find the bounding box of transparent areas.
     */
    private function floodFill($image, $startX, $startY, &$visited): ?array
    {
        $width   = imagesx($image);
        $height  = imagesy($image);
        $queue   = [[$startX, $startY]];
        $minX    = $maxX = $startX;
        $minY    = $maxY = $startY;
        $found   = false;

        while (!empty($queue)) {
            [$x, $y] = array_pop($queue);

            if ($x < 0 || $x >= $width || $y < 0 || $y >= $height || $visited[$y][$x]) {
                continue;
            }

            $visited[$y][$x] = true;
            $rgba  = imagecolorat($image, $x, $y);
            $alpha = ($rgba & 0x7F000000) >> 24;

            if ($alpha < 127) continue;

            $found = true;
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);

            $queue[] = [$x + 1, $y];
            $queue[] = [$x - 1, $y];
            $queue[] = [$x, $y + 1];
            $queue[] = [$x, $y - 1];
        }

        if (!$found) return null;

        return [
            'x'      => $minX,
            'y'      => $minY,
            'width'  => $maxX - $minX + 1,
            'height' => $maxY - $minY + 1,
        ];
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(string $id)
    {
        $template   = Template::find($id);
        return Inertia::render('templates/Edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Template $template)
    {
        // Validasi input
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png|max:4096',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            Storage::disk('public')->delete($template->image_path);

            // Simpan file baru
            $file     = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('templates', $filename, 'public');
            $fullPath = storage_path('app/public/' . $path);

            // Pastikan folder publik tersedia
            if (!File::exists(public_path('templates'))) {
                File::makeDirectory(public_path('templates'), 0755, true);
            }

            // Validasi PNG & deteksi transparansi
            $image = @imagecreatefrompng($fullPath);
            if (!$image) {
                return back()->with('error', 'Gagal memproses gambar. Pastikan file PNG valid.');
            }
            imagesavealpha($image, true);
            $layoutJson = $this->detectTransparentAreas($image);

            // Update path dan layout baru
            $data['image_path']  = $path;
            $data['layout_json'] = json_encode($layoutJson);
        }

        $template->update($data);

        return redirect()->route('templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(Template $template)
    {
        Storage::disk('public')->delete($template->image_path);
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template berhasil dihapus.');
    }

    /**
     * API endpoint to get all templates.
     */
    public function apiIndex()
    {
        return response()->json(Template::all());
    }
}
