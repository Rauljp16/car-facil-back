<?php

namespace App\Http\Controllers;

use App\Models\Coche;
use Illuminate\Http\Request;

class CocheController extends Controller
{

    public function index()
    {
        $coches = Coche::all();
        return response()->json($coches);
    }


    public function show($id)
    {
        $coche = Coche::findOrFail($id);
        return response()->json($coche);
    }


    public function store(Request $request)
    {
        $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'foto' => 'nullable|string',
        ]);

        $coche = Coche::create($request->all());
        return response()->json($coche, 201);
    }


    public function update(Request $request, $id)
    {
        $coche = Coche::findOrFail($id);
        $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'foto' => 'nullable|string',
        ]);

        $coche->update($request->all());
        return response()->json($coche);
    }


    public function destroy($id)
    {
        $coche = Coche::findOrFail($id);
        $coche->delete();
        return response()->json(null, 204);
    }
}


