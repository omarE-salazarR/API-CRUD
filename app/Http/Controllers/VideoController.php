<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use App\Services\GptService;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Controlador para gestionar los videos.
 * 
 * Autor: Omar Salzar
 * Correo: omar.esr.901@gmail.com
 * Fecha: 2024-09-07
 */
class VideoController extends Controller
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
     * Mostrar una lista de los recursos.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $videos = Video::paginate(10);
            return response()->json($videos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Almacenar un nuevo recurso en el almacenamiento.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validar el tipo de creación del video
            $validated = $request->validate([
                'type' => 'required|string|in:manual,auto',
            ]);

            if ($request->type == 'manual') {
                // Validar los datos del video manual
                $request->validate([
                    'title' => 'required|string|max:255',
                    'url' => 'required|url',
                    'description' => 'nullable|string',
                ]);

                $video = Video::create([
                    'title' => $request->input('title'),
                    'url' => $request->input('url'),
                    'description' => $request->input('description', ''),
                ]);

                return response()->json(['response' => 'Video creado', 'errors' => []], 201);
            } else {
                return $this->storeGpt($request);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'La creación falló', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Almacenar un recurso utilizando GPT.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeGpt(Request $request)
    {
        try {
            $prompt = 'Genera un registro de video ficticio con un título, URL y descripción. El resultado debe estar separado por "|" y ser una sola cadena de texto. Ejemplo: <<titulo del video>>|<<url del video>>|<<descripcion>>';
            $gptData = $this->gptService->fetchData($prompt);

            if (isset($gptData['choices'][0]['message']['content'])) {
                $videoData = $this->parseVideoData($gptData['choices'][0]['message']['content']);
                $video = Video::create([
                    'title' => $videoData['title'],
                    'url' => $videoData['url'],
                    'description' => $videoData['description']
                ]);
                return response()->json($video, 201);
            }
            return response()->json(['error' => 'No se pudo generar el video', 'errors'=> $gptData], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en la generación del video'], 500);
        }
    }

    /**
     * Mostrar un recurso específico.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Video no encontrado'], 404);
            }
            return response()->json($video);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Video no encontrado'], 404);
        }
    }

    /**
     * Actualizar el recurso especificado en el almacenamiento.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Video no encontrado'], 404);
            }

            // Validar los datos para la actualización del video
            $request->validate([
                'title' => 'required|string|max:255',
                'url' => 'required|url',
                'description' => 'nullable|string',
            ]);

            $video->update([
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'description' => $request->input('description', ''),
            ]);

            return response()->json($video, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Video no encontrado'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Error de validación'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Eliminar el recurso especificado del almacenamiento.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Video no encontrado'], 404);
            }
            $video->delete();
            return response()->json(['message' => 'Video eliminado con éxito']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Video no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Parsear los datos del video generados por GPT.
     * 
     * @param  string  $gptResponseText
     * @return array
     */
    private function parseVideoData($gptResponseText)
    {
        $data = explode("|", $gptResponseText);
        return [
            'title' => trim($data[0]),
            'url' => trim($data[1]),
            'description' => trim($data[2]),
        ];
    }
}
