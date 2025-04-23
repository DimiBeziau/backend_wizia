<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class C_IAController extends Controller
{
    private $apiUrl;
    private $model;
    private $prompt;
    private $stream;
    private $keyApi;
    private $geminiApiUrl;

    public function __construct() {
        $this->apiUrl ="http://localhost:11434/api/generate";
        $this->model = "llama3.2";
        $this->stream =false;
        $this->keyApi=env('KEY_API_GEMINI'); // Pour GEMINI
        $this->geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $this->keyApi;
    }
    public function generatprompt(Request $promptClient){
        $this ->prompt = $promptClient;
        $data = json_encode([
            "model" => $this->model,
            "prompt" => $this ->prompt,
            "stream"=> $this->stream,
        ]);
        $ch = curl_init($this->apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $decodeJson = json_decode($response, true);
        return $decodeJson['response'] ?? "Erreur : reponse introuvable"; 
    }
    public function generatpromptgemini( Request $promptClient){ 

        $validated = $promptClient->validate([
            'prompt' => 'required|string',
        ]);
            $this->prompt = $validated['prompt'];
            $data = json_encode([
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $this->prompt]
                        ]
                    ]
                ]
            ]);
          $ch = curl_init($this->geminiApiUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
              "Content-Type: application/json"
          ]);
          curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $reponse = curl_exec($ch);
        curl_close($ch);

        $decodeJson = json_decode($reponse,true);
        return $decodeJson['candidates'][0]['content']['parts'][0]['text'] ?? "Erreur de la génération du prompte gemini";

            
    }
}
