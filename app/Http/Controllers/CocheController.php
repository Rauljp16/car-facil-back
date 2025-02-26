<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;

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
            'foto' => 'required|array|min:1',
            'foto.*' => 'url',
        ]);

        $coche = Coche::create($request->only(['marca', 'modelo', 'anio', 'precio']));

        $coche->images()->create([
            'image_path' => json_encode($request->foto),
        ]);

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
                'foto' => 'nullable|array',
                'foto.*' => 'url',
            ]);

            $coche->update($request->only(['marca', 'modelo', 'anio', 'precio']));
            \Log::info("Coche actualizado: " . json_encode($coche));

            if ($request->has('foto')) {
                $coche->images()->updateOrCreate(
                    ['coche_id' => $coche->id],
                    ['image_path' => json_encode($request->foto)]
                );
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
        if ($coche->images->isEmpty()) {
            return [];
        }

        $imagePath = $coche->images->first()->image_path;

        // Si image_path ya es un array, lo devolvemos directamente
        if (is_array($imagePath)) {
            return $imagePath;
        }

        // Si es una cadena JSON, la decodificamos
        $decodedPath = json_decode($imagePath, true);

        // Si la decodificación produce un array, lo devolvemos
        if (is_array($decodedPath)) {
            return $decodedPath;
        }

        // Si no es un array ni JSON válido, lo devolvemos como un array de un solo elemento
        return [$imagePath];
    }
}
