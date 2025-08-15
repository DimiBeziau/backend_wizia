<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class C_IAController extends Controller
{
  private $apiUrl;
  private $model;
  private $prompt;
  private $size;
  private $stream;
  private $keyApigemini;
  private $geminiApiUrl;
  private $gptApiUrl;
  private $keyApigpt;

  public function __construct()
  {
    $this->apiUrl = "http://localhost:11434/api/generate";
    $this->model = "llama3.2";
    $this->stream = false;
    $this->keyApigemini = env('KEY_API_GEMINI'); // Pour GEMINI AIzaSyBquuVdy6a4vvu4mUfuuyEP0CoI8P6SyPY
    $this->keyApigpt = env('key_API_GPT'); 
    $this->geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $this->keyApigemini;
    $this->gptApiUrl = "https://api.openai.com/v1/images/generations";
  }
  public function generatprompt(Request $promptClient)
  {
    $this->prompt = $promptClient;
    $data = json_encode([
      "model" => $this->model,
      "prompt" => $this->prompt,
      "stream" => $this->stream,
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

  public function generatPictureGPT(Request $promptClient)
  {
    $this->prompt = $promptClient->input('prompt');
    $this->size = $promptClient->input('size', '256x256'); // Default size if not provided
    $data =[
      "prompt" => $this->prompt,
      "n"=>1,
      "size" => $this->size,
      "response_format" => "url"  
    ];
    //256x256 → petite image (rapide, peu de détails)
    //512x512 → taille moyenne (bon équilibre rapidité/qualité)
    //1024x1024 → grande image carrée (par défaut dans ton code)
    //1024x1792 → portrait (format vertical)
   
    $ch = curl_init($this->gptApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $this->keyApigpt 
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $decodeJson = json_decode($response, true);

    if (isset($decodeJson['data'][0]['url'])) {
        return response()->json(['image_url' => $decodeJson['data'][0]['url']]);
    } else {
        return response()->json(['error' => 'Image non générée', 'details' => $decodeJson], 500);
    }

  }


  public function generatpromptgemini(Request $request)
  {

    $request->validate([
      'prompt' => 'required|string',
    ]);

    $prompt = $request->input('prompt');

    $data = json_encode([
      "contents" => [
        [
          "parts" => [
            ["text" => $prompt]
          ]
        ]
      ]
    ]);

    $ch = curl_init($this->geminiApiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return response()->json(['error' => $error_msg], 500);
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if (!isset($decoded['candidates'])) {
      return response()->json(['error' => 'Erreur de la génération du prompt Gemini'], 500);
    }

    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Réponse vide';

    return response()->json(['text' => $text]);
  }
  
}
