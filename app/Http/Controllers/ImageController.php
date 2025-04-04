<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Coche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(Request $request, $cocheId)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $coche = Coche::findOrFail($cocheId);
        $uploadPath = "coches/{$cocheId}";

        Storage::disk('public')->makeDirectory($uploadPath);

        $savedImages = [];
        foreach ($request->file('images') as $image) {
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();

            $image->storeAs($uploadPath, $filename, 'public');

            $savedImages[] = $coche->images()->create([
                'image_path' => "$uploadPath/$filename"
            ]);
        }

        return response()->json([
            'message' => 'Imágenes subidas correctamente',
            'images' => $savedImages
        ]);
    }
}
