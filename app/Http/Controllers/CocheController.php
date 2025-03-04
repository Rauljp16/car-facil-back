<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CocheController extends Controller
{
    private $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

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

        if (!$request->hasFile('images')) {
            return response()->json(['error' => 'No se recibieron archivos de imagen'], 422);
        }

        try {
            $validated = $request->validate([
                'marca' => 'required|string|max:255',
                'modelo' => 'required|string|max:255',
                'anio' => 'required|integer',
                'precio' => 'required|numeric',
                'images' => 'required|array',
                'images.*' => 'required|file|max:5120',
                    ]);


            $coche = Coche::create($request->only(['marca', 'modelo', 'anio', 'precio']));

            if ($request->hasFile('images')) {
                $imageCount = count($request->file('images'));

                foreach ($request->file('images') as $index => $image) {
                    $filename = uniqid() . '.webp';
                    $path = 'images/cars/' . $filename;

                    $processedImage = $this->processImage($image);

                    $processedImage->save(storage_path('app/public/' . $path));

                    $coche->images()->create(['image_path' => $path]);
                }
            }

            return response()->json($this->formatCoche($coche), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {

        try {
            $coche = Coche::findOrFail($id);

            $request->validate([
                'marca' => 'sometimes|required|string|max:255',
                'modelo' => 'sometimes|required|string|max:255',
                'anio' => 'sometimes|required|integer',
                'precio' => 'sometimes|required|numeric',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $coche->update($request->only(['marca', 'modelo', 'anio', 'precio']));

            if ($request->hasFile('images')) {
                foreach ($coche->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage->image_path);
                    $oldImage->delete();
                }

                foreach ($request->file('images') as $index => $image) {
                    $filename = uniqid() . '.webp';
                    $path = 'images/cars/' . $filename;

                    $processedImage = $this->processImage($image);
                    $processedImage->save(storage_path('app/public/' . $path));

                    $coche->images()->create([
                        'image_path' => $path,
                    ]);
                }
            }

            $formattedCoche = $this->formatCoche($coche);

            return response()->json($formattedCoche);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $coche = Coche::findOrFail($id);

            foreach ($coche->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $coche->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el coche'], 500);
        }
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

    private function processImage($image)
    {
        try {
            return $this->imageManager->read($image)
                ->scaleDown(width: 800)
                ->toWebp(quality: 75);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
