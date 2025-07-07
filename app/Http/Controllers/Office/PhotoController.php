<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function recent()
    {
        $files = collect(Storage::disk('public')->files('photos'))
            ->sortByDesc(fn($f) => Storage::disk('public')->lastModified($f))
            ->take(6)
            ->map(fn($f) => basename($f));

        return response()->json($files);
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
            'photos.*' => 'required|image|mimes:jpeg,jpg,png|max:4096',
        ]);

        foreach ($request->file('photos') as $photo) {
            $photo->storeAs('photos', uniqid('photo_') . '.jpg', 'public');
        }

        return response()->json(['status' => 'ok']);
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
