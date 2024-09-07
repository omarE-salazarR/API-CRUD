<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use App\Services\GptService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Controlador para gestionar los desafíos.
 * 
 * Autor: Omar Salzar
 * Correo: omar.esr.901@gmail.com
 * Fecha: 2024-09-07
 */
class ChallengeController extends Controller
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
     * Mostrar una lista de los desafíos.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $challenges = Challenge::paginate(10);
            return response()->json($challenges);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Almacenar un nuevo desafío en la base de datos.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validar el tipo de desafío
            $validatedType = $request->validate([
                'type' => 'required|string|in:manual,auto',
            ]);

            if ($request->type == 'manual') {
                // Validar los datos del desafío manual
                $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                ]);
                
                $challenge = Challenge::create([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                ]);

                return response()->json(['response' => 'Challenge creado', 'errors' => []], 201);
            } else {
                return $this->storeGpt($request);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'La creación falló', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Almacenar un nuevo desafío utilizando GPT.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeGpt(Request $request)
    {    
        $prompt = 'retorna un registro aleatorio que tenga un titulo y descripcion larga de un reto ficticio separado por "|", responde solo con el string ejemplo: <<titulo de reto>>|<<descripcion>>';
        $gptData = $this->gptService->fetchData($prompt);

        if (isset($gptData['choices'][0]['message']['content'])) {
            $challengeData = $this->parseChallengeData($gptData['choices'][0]['message']['content']);
            $challenge = Challenge::create([
                'title' => $challengeData['title'],
                'description' => $challengeData['description']
            ]);
            return response()->json($challenge, 201);
        }

        return response()->json(['error' => 'No se pudo generar el desafío', 'errors' => $gptData], 500);
    }

    /**
     * Mostrar el desafío especificado.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $challenge = Challenge::find($id);
            if (!$challenge) {
                return response()->json(['error' => 'Challenge no encontrado'], 404);
            }
            return response()->json($challenge);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Challenge no encontrado'], 404);
        }
    }

    /**
     * Actualizar el desafío especificado.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $challenge = Challenge::find($id);
            if (!$challenge) {
                return response()->json(['error' => 'Challenge no encontrado'], 404);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

            $challenge->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ]);

            return response()->json($challenge, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Challenge no encontrado'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Error de validación'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Eliminar el desafío especificado.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $challenge = Challenge::find($id);
            if (!$challenge) {
                return response()->json(['error' => 'Challenge no encontrado'], 404);
            }
            $challenge->delete();
            return response()->json(['message' => 'Challenge eliminado con éxito']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Challenge no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Parsear los datos del desafío generados por GPT.
     * 
     * @param  string  $gptResponseText
     * @return array
     */
    private function parseChallengeData($gptResponseText)
    {
        $data = explode("|", $gptResponseText);
        return [
            'title' => trim($data[0]),
            'description' => trim($data[1]),
        ];
    }
}
