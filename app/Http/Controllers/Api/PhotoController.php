<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Photo;
use App\Models\Template;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $photos = Photo::latest()->get();
        return Inertia::render('LandingPage', []);
    }
    
    /**
     * Get Recent Taken Photos
     */
    public function recent()
    {
        $photos = Photo::latest()->take(6)->get(['id', 'file_path']);
        return response()->json($photos);
    }

    /**
     * Render the template with the selected photo.
     */
    public function renderTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'photo_path' => 'required|string',
        ]);

        $template = Template::findOrFail($request->input('template_id'));

        return Inertia::render('PhotoSession', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'file_path' => asset('templates/' . $template->file_path),
                'layout_json' => $template->layout_json,
            ],
            'photo_path' => $request->input('photo_path'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $photoPath = $request->file('photo')->store('photos', 'public');

        $photo = Photo::create([
            'file_path' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'photo_id' => $photo->id,
            'file_path' => asset('storage/' . $photoPath),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
