<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Photo;
use Inertia\Inertia;

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
        $photos = $request->file('photos');

        foreach ($photos as $photo) {
            $photo->storeAs('photos', uniqid() . '.jpg', 'public');
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
