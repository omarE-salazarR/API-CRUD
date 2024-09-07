<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;

class GptService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GPT_API_KEY');
    }

    public function fetchData($query)
    {
        

      

      // return  $response = Http::withHeaders([
      //      'Authorization' => 'Bearer ' . $this->apiKey,
      //  ])->get('https://api.openai.com/v1/models');

      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => $query]
        ],
        'max_tokens' => 50,   // Ajusta según el tamaño máximo de la respuesta deseada
        'temperature' => 0.1, // Baja aleatoriedad para respuestas más precisas
        'top_p' => 0.5,       // Limita el rango de tokens considerados
        'stop' => ['\n']      // O especifica otros tokens para detener la respuesta
    ]);
        return $response->json();
    }
}
