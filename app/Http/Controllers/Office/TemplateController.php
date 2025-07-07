<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('templates', 'public');

        Template::create([
            'name' => $request->name,
            'image_path' => $path,
            'layout_json' => "[{\"x\":100,\"y\":70,\"width\":510,\"height\":380},{\"x\":100,\"y\":545,\"width\":510,\"height\":380},{\"x\":100,\"y\":1020,\"width\":510,\"height\":380},{\"x\":100,\"y\":1495,\"width\":510,\"height\":380}]",
        ]);

        return redirect()->route('templates.index')->with('success', 'Template berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Template $template)
    {
        return Inertia::render('templates/Edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('image')) {
            // Hapus file lama
            Storage::disk('public')->delete($template->image_path);

            $data['image_path'] = $request->file('image')->store('templates', 'public');
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
}
