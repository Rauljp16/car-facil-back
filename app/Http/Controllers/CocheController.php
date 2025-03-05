<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CocheController extends Controller
{
    public function index()
    {
        return response()->json(Coche::with('images')->get());
    }

    public function show($id)
    {
        return response()->json(Coche::with('images')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'images.*' => 'file|max:5120',
        ]);

        $coche = Coche::create($validated);

        if ($request->hasFile('images')) {
            $this->handleImages($coche, $request->file('images'));
        }

        return response()->json($coche->load('images'), 201);
    }

    public function update(Request $request, $id)
    {
        $coche = Coche::findOrFail($id);

        try {
            $validated = $request->validate([
                'marca' => 'sometimes|string|max:255',
                'modelo' => 'sometimes|string|max:255',
                'anio' => 'sometimes|integer',
                'precio' => 'sometimes|numeric',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        $coche->fill($request->only(['marca', 'modelo', 'anio', 'precio']));

        if ($coche->isDirty()) {
            $coche->save();
        }

        if ($request->hasFile('images')) {
            $this->deleteImages($coche);
            $this->handleImages($coche, $request->file('images'));
        }

        return response()->json($coche->load('images'));
    }

    public function destroy($id)
    {
        $coche = Coche::findOrFail($id);
        $this->deleteImages($coche);
        $coche->delete();

        return response()->json(null, 204);
    }

    private function handleImages(Coche $coche, $images)
    {
        foreach ($images as $image) {
            $filename = uniqid() . '.webp';
            $path = 'images/cars/' . $filename;
            $this->processImage($image)->save(storage_path('app/public/' . $path));

            $coche->images()->create(['image_path' => $path]);
        }
    }

    private function deleteImages(Coche $coche)
    {
        foreach ($coche->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    private function processImage($image)
    {
        return (new ImageManager(new Driver()))->read($image)
            ->scaleDown(width: 800)
            ->toWebp(quality: 75);
    }
}
