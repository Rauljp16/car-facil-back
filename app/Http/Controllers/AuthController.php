<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $authenticatedUser = auth()->user();

        if (!$authenticatedUser || $authenticatedUser->email !== 'rauljp16@gmail.com') {
            return response()->json([
                'error' => 'Acceso denegado: se requiere autenticación y permisos de superadmin'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }


    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        return response()->json(['token' => $token]);
    }


    public function logout()
    {

        JWTAuth::invalidate(JWTAuth::getToken());


        return response()->json(['message' => 'Logout exitoso']);
    }
}
