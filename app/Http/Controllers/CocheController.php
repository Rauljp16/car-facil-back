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
        $coches = Coche::with('images')->latest()->get()->map(function ($coche) {
            return $this->formatCocheResponse($coche);
        });
        return response()->json($coches);
    }

    public function show($id)
    {
        $coche = Coche::with('images')->findOrFail($id);
        return response()->json($this->formatCocheResponse($coche));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'images.*' => 'file|max:2048',
            'motor' => 'required|integer',
            'cv' => 'required|integer',
            'cambio' => 'required|string',
            'plazas' => 'required|integer',
            'puertas' => 'required|integer',
            'combustible' => 'required|string',
    ]);

        $coche = Coche::create($validated);

        if ($request->hasFile('images')) {
            $this->handleImages($coche, $request->file('images'));
        }

        return response()->json($this->formatCocheResponse($coche), 201);
    }

    public function update(Request $request, $id)
    {
        $coche = Coche::findOrFail($id);

        try {
            $validated = $request->validate([
                'marca' => 'string|max:255',
                'modelo' => 'string|max:255',
                'anio' => 'integer',
                'precio' => 'numeric',
                'images.*' => 'file|max:2048',
                'motor' => 'integer',
                'cv' => 'integer',
                'cambio' => 'string|max:255',
                'plazas' => 'integer',
                'puertas' => 'integer',
                'combustible' => 'string|max:255',
                ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        $coche->fill($request->only(['marca', 'modelo', 'anio', 'precio', 'motor', 'cv', 'cambio', 'plazas', 'puertas', 'combustible']));

        if ($coche->isDirty()) {
            $coche->save();
        }

        if ($request->hasFile('images')) {
            $this->deleteImages($coche);
            $this->handleImages($coche, $request->file('images'));
        }

        return response()->json($this->formatCocheResponse($coche));
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

            $coche->images()->create([
                'image_path' => $path,
            ]);
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

    private function formatCocheResponse($coche)
    {
        return [
            'id' => $coche->id,
            'marca' => $coche->marca,
            'modelo' => $coche->modelo,
            'anio' => $coche->anio,
            'precio' => $coche->precio,
            'motor' => $coche->motor,
            'cv' => $coche->cv,
            'cambio' => $coche->cambio,
            'plazas' => $coche->plazas,
            'puertas' => $coche->puertas,
            'combustible' => $coche->combustible,
            'images' => $coche->images->map(function ($image) {
                return url('storage/' . $image->image_path);
            }),
        ];
    }
}
