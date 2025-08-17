<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index()
    {
        return Template::latest()->paginate(20);
    }

    public function show(Template $template)
    {
        return $template;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'canvas_width' => 'required|integer|min:100',
            'canvas_height' => 'required|integer|min:100',
            'layout_json' => 'required|array|min:1',
            'background' => 'nullable|file|image|max:8192',
        ]);

        $path = null;
        if ($request->hasFile('background')) {
            $path = $request->file('background')->store('templates', 'public');
        }

        $template = Template::create([
            'name' => $data['name'],
            'canvas_width' => $data['canvas_width'],
            'canvas_height' => $data['canvas_height'],
            'background_path' => $path,
            'layout_json' => $data['layout_json'],
        ]);

        return response()->json(['message' => 'Template saved', 'id' => $template->id]);
    }

    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'canvas_width' => 'sometimes|integer|min:100',
            'canvas_height' => 'sometimes|integer|min:100',
            'layout_json' => 'sometimes|array|min:1',
            'background' => 'nullable|file|image|max:8192',
        ]);

        if ($request->hasFile('background')) {
            if ($template->background_path) Storage::disk('public')->delete($template->background_path);
            $template->background_path = $request->file('background')->store('templates', 'public');
        }

        foreach (['name','canvas_width','canvas_height','layout_json'] as $key) {
            if (array_key_exists($key, $data)) $template->{$key} = $data[$key];
        }

        $template->save();
        return response()->json(['message' => 'Template updated']);
    }

    public function destroy(Template $template)
    {
        if ($template->background_path) Storage::disk('public')->delete($template->background_path);
        $template->delete();
        return response()->json(['message' => 'Template deleted']);
    }
}
