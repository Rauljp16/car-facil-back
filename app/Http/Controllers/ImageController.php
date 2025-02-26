<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Coche;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request, $cocheId)
    {
        if (!is_numeric($cocheId)) {
            return response()->json(['error' => 'ID de coche inválido.'], 400);
        }

        $request->validate([
            'image' => 'required|array|min:1',
            'image.*' => 'url',
        ]);

        $coche = Coche::findOrFail($cocheId);

        foreach ($request->image as $imageUrl) {
            $coche->images()->create([
                'image_path' => $imageUrl,
            ]);
        }

        $coche->load('images');

        return response()->json([
            'message' => 'Imagen(es) guardada(s) correctamente.',
            'images' => $coche->images
        ]);
    }
}
