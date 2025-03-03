<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CocheController extends Controller
{
    public function index()
    {
        $coches = Coche::with('images')->get();
        $formattedCoches = $coches->map(function ($coche) {
            return $this->formatCoche($coche);
        });
        return response()->json($formattedCoches);
    }

    public function show($id)
    {
        $coche = Coche::with('images')->findOrFail($id);
        return response()->json($this->formatCoche($coche));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $coche = Coche::create($request->only(['marca', 'modelo', 'anio', 'precio']));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images/cars', 'public');
                $coche->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json($this->formatCoche($coche), 201);
    }

    public function update(Request $request, $id)
    {
        \Log::info("Iniciando método update para ID: " . $id);
        \Log::info("Datos recibidos: " . json_encode($request->all()));

        try {
            $coche = Coche::findOrFail($id);
            \Log::info("Coche encontrado: " . $coche->id);

            $request->validate([
                'marca' => 'sometimes|required|string|max:255',
                'modelo' => 'sometimes|required|string|max:255',
                'anio' => 'sometimes|required|integer',
                'precio' => 'sometimes|required|numeric',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $coche->update($request->only(['marca', 'modelo', 'anio', 'precio']));
            \Log::info("Coche actualizado: " . json_encode($coche));

            if ($request->hasFile('images')) {
                foreach ($coche->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage->image_path);
                    $oldImage->delete();
                }

                foreach ($request->file('images') as $image) {
                    $path = $image->store('images/cars', 'public');
                    $coche->images()->create([
                        'image_path' => $path,
                    ]);
                }
                \Log::info("Imágenes actualizadas");
            }

            $formattedCoche = $this->formatCoche($coche);
            \Log::info("Respuesta final: " . json_encode($formattedCoche));

            return response()->json($formattedCoche);
        } catch (\Exception $e) {
            \Log::error("Error en update: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $coche = Coche::findOrFail($id);

        foreach ($coche->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $coche->delete();
        return response()->json(null, 204);
    }

    private function formatCoche($coche)
    {
        return [
            'id' => $coche->id,
            'marca' => $coche->marca,
            'modelo' => $coche->modelo,
            'anio' => $coche->anio,
            'precio' => $coche->precio,
            'created_at' => $coche->created_at,
            'updated_at' => $coche->updated_at,
            'images' => $this->getImageUrls($coche),
        ];
    }

    private function getImageUrls($coche)
    {
        return $coche->images->map(function ($image) {
            return asset('storage/' . $image->image_path);
        })->toArray();
    }
}
