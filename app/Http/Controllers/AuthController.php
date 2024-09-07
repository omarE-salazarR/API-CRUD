<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

/**
 * Controlador para gestionar la autenticación de usuarios.
 * 
 * Autor: Omar Salzar
 * Correo: omar.esr.901@gmail.com
 * Fecha: 2024-09-07
 */
class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            // Validar los datos de registro del usuario
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
            ]);
    
            // Crear el nuevo usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($user) {
                return response()->json(['response' => 'Usuario creado', 'errors' => []], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'La validación falló', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Iniciar sesión de un usuario.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // Intentar crear un token JWT para el usuario
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(compact('token'));
    }

    /**
     * Cerrar sesión del usuario.
     * 
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        // Invalidar el token JWT del usuario
        JWTAuth::invalidate(JWTAuth::parseToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
