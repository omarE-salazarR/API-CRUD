<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use App\Services\GptService;

/**
 * Controlador para gestionar los usuarios.
 * 
 * Autor: Omar Salzar
 * Correo: omar.esr.901@gmail.com
 * Fecha: 2024-09-07
 */
class UserController extends Controller
{
    protected $gptService;

    /**
     * Crear una nueva instancia del controlador.
     * 
     * @param  \App\Services\GptService  $gptService
     * @return void
     */
    public function __construct(GptService $gptService)
    {
        $this->gptService = $gptService;
    }

    /**
     * Obtener todos los usuarios con paginación.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::paginate(10);
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear un nuevo usuario.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validar el tipo de creación del usuario
            $validatedType = $request->validate([
                'type' => 'required|string|in:manual,auto',
            ]);

            if ($request->type == 'manual') {
                // Validar los datos del usuario manual
                $validated = $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|string|email|unique:users',
                    'password' => 'required|string|min:6',
                ]);

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                return response()->json(['response' => 'Usuario creado', 'errors' => []], 201);
            } else {
                return $this->storeGpt($request);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'La creación falló', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Crear un usuario utilizando GPT.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeGpt(Request $request)
    {    
        $prompt = 'retorna un registro de usuario aleatorio que tenga nombre, correo y contraseña, separalos por "|" y concatena al correo esto: correo_'.date('YmdHis').'@mail.com, solo responde con el string ';
        $gptData = $this->gptService->fetchData($prompt);

        if (isset($gptData['choices'][0]['message']['content'])) {
            $userData = $this->parseUserData($gptData['choices'][0]['message']['content']);
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            return response()->json($user, 201);
        }

        return response()->json(['error' => 'No se pudo generar el usuario', 'errors' => $gptData], 500);
    }

    /**
     * Mostrar un usuario específico.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }

    /**
     * Actualizar un usuario.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Validar los datos para la actualización del usuario
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:6',
            ]);

            $user->update($request->only(['name', 'email']));

            // Actualizar la contraseña si se proporciona
            if ($request->password) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Error de validación'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Eliminar un usuario.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            $user->delete();

            return response()->json(['message' => 'Usuario eliminado con éxito']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Parsear los datos del usuario generados por GPT.
     * 
     * @param  string  $gptResponseText
     * @return array
     */
    private function parseUserData($gptResponseText)
    {
        $data = explode("|", $gptResponseText);
        return [
            'name' => trim($data[0]),
            'email' => trim($data[1]),
            'password' => trim($data[2]),
        ];
    }
}
