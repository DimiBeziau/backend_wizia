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
    $this->keyApigemini = env('KEY_API_GEMINI'); 
    $this->keyApigpt = env('key_API_GPT'); 
    $this->geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $this->keyApigemini;
    $this->gptApiUrl = "https://api.openai.com/v1/images/generations";
  }

  /**
   * @OA\Post(
   *     path="/ia/generateIALocal",
   *     summary="Génère une réponse texte avec Llama3.2",
   *     tags={"Intelligence artificielle"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"prompt"},
   *             @OA\Property(property="prompt", type="string", example="Explique-moi Laravel en quelques mots")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Réponse générée avec succès",
   *         @OA\JsonContent(
   *             @OA\Property(property="text", type="string", example="Laravel est un framework PHP...")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Erreur lors de la génération",
   *         @OA\JsonContent(
   *             @OA\Property(property="error", type="string", example="Erreur de la génération du prompt")
   *         )
   *     )
   * )
   */
  public function generatprompt(Request $request)
  {
       $this->prompt = $request->input('prompt');
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

    //$decodeJson = json_decode($response, true);
     $decoded = json_decode($response, true);

    // if (!isset($decoded['candidates'])) {
    //   return response()->json(['error' => 'Erreur de la génération du prompt'], 500);
    // }

    // $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Réponse vide';

    return response()->json(['text' => $decoded['response'] ?? 'Réponse vide']);
    //return $decodeJson['response'] ?? "Erreur : reponse introuvable";
  }

   /**
   * @OA\Post(
   *     path="/ia/generateIApicture",
   *     summary="Génère une image avec DALL-E 3",
   *     tags={"Intelligence artificielle"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"prompt"},
   *             @OA\Property(property="prompt", type="string", example="Un chat astronaute dans l'espace"),
   *             @OA\Property(property="size", type="string", example="1024x1024", description="Tailles disponibles: 256x256, 512x512, 1024x1024, 1024x1792")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Image générée avec succès",
   *         @OA\JsonContent(
   *             @OA\Property(property="image_url", type="string", example="https://oaidalleapiprodscus.blob.core.windows.net/...")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Erreur lors de la génération de l'image",
   *         @OA\JsonContent(
   *             @OA\Property(property="error", type="string", example="Image non générée"),
   *             @OA\Property(property="details", type="object")
   *         )
   *     )
   * )
   */

  public function generatPictureGPT(Request $promptClient)
  {
    $this->prompt = $promptClient->input('prompt');
    $this->size = $promptClient->input('size', '1024x1024'); 
    $this->model = "dall-e-3";
    $data =[
      "prompt" => $this->prompt,
      "n"=>1,
      "size" => $this->size,
      "response_format" => "url" ,
      "model" => $this->model, 
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

    $imageUrl = $decodeJson['data'][0]['url'];
    $imageContent = file_get_contents($imageUrl);

    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!$extension) {
        $extension = 'jpg';
    }
    $imageName = uniqid('img_') . '.' . $extension;

    $savePath = storage_path('app/public/NetworkPicture/' . $imageName);

    file_put_contents($savePath, $imageContent);

    $url = env('APP_URL') . '/storage/NetworkPicture/' . $imageName;
    return response()->json(['image_url' => $url]);
    } else {
        return response()->json(['error' => 'Image non générée', 'details' => $decodeJson], 500);
    }

  }
  /**
   * @OA\Post(
   *     path="/ia/generateIA",
   *     summary="Génère un post structuré pour les réseaux sociaux avec Gemini",
   *     tags={"Intelligence artificielle"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"prompt"},
   *             @OA\Property(property="prompt", type="string", example="Crée un post Instagram sur le café artisanal")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Post généré avec succès",
   *         @OA\JsonContent(
   *             @OA\Property(property="title", type="string", example="Le café artisanal : un art à découvrir"),
   *             @OA\Property(property="content", type="string", example="Découvrez les secrets du café de spécialité...")
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Erreur de validation",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="The prompt field is required.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Erreur lors de la génération",
   *         @OA\JsonContent(
   *             @OA\Property(property="error", type="string", example="Erreur de la génération du prompt Gemini")
   *         )
   *     )
   * )
   */
public function generatpromptgemini(Request $request)
{
    $request->validate([
        'prompt' => 'required|string',
    ]);

    $prompt = $request->input('prompt');
    $type = $request->input('type');

    if ($type === 'newsletter') {
        $schema = [
            "type" => "object",
            "properties" => [
                "subject" => [
                    "type" => "string",
                    "description" => "Le sujet de la newsletter"
                ],
                "body" => [
                    "type" => "string",
                    "description" => "Le corps de la newsletter au format HTML"
                ],
                "altBody" => [
                    "type" => "string",
                    "description" => "Le corps de la newsletter en texte simple"
                ]
            ],
            "required" => ["subject", "body", "altBody"]
        ];
    } else {
        // Définir le schéma JSON souhaité pour les posts (par défaut)
        $schema = [
            "type" => "object",
            "properties" => [
                "title" => [
                    "type" => "string",
                    "description" => "Le titre du post pour les réseaux sociaux"
                ],
                "content" => [
                    "type" => "string",
                    "description" => "Le contenu du post pour les réseaux sociaux"
                ]
            ],
            "required" => ["title", "content"]
        ];
    }

    $data = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "response_mime_type" => "application/json",
            "response_schema" => $schema
        ]
    ]);

    // Ensure key is loaded
    if (empty($this->keyApigemini)) {
        $this->keyApigemini = env('KEY_API_GEMINI');
    }

    if (empty($this->keyApigemini)) {
        return response()->json(['error' => 'Erreur configuration : La clé API Gemini (KEY_API_GEMINI) est manquante.'], 500);
    }

    // Reconstruct URL to ensure key is present
    $this->geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $this->keyApigemini;

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
        return response()->json([
            'error' => 'Erreur de la génération du prompt Gemini',
            'details' => $decoded
        ], 500);
    }

    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
    // Décoder la réponse JSON structurée
    $structuredResponse = json_decode($text, true);

    if ($type === 'newsletter') {
        return response()->json([
            'subject' => $structuredResponse['subject'] ?? '',
            'body' => $structuredResponse['body'] ?? '',
            'altBody' => $structuredResponse['altBody'] ?? ''
        ]);
    } else {
        return response()->json([
            'title' => $structuredResponse['title'] ?? '',
            'content' => $structuredResponse['content'] ?? ''
        ]);
    }
}
  // public  function generatpromptgemini(Request $request)
  // {

  //   $request->validate([
  //     'prompt' => 'required|string',
  //   ]);

  //   $prompt = $request->input('prompt');

  //   $data = json_encode([
  //     "contents" => [
  //       [
  //         "parts" => [
  //           ["text" => $prompt]
  //         ]
  //       ]
  //     ]
  //   ]);

  //   $ch = curl_init($this->geminiApiUrl);

  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     "Content-Type: application/json",
  //     "Accept: application/json"
  //   ]);
  //   curl_setopt($ch, CURLOPT_POST, true);
  //   curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

  //   $response = curl_exec($ch);

  //   if (curl_errno($ch)) {
  //     $error_msg = curl_error($ch);
  //     curl_close($ch);
  //     return response()->json(['error' => $error_msg], 500);
  //   }

  //   curl_close($ch);

  //   $decoded = json_decode($response, true);

  //   if (!isset($decoded['candidates'])) {
  //     return response()->json(['error' => 'Erreur de la génération du prompt Gemini'], 500);
  //   }

  //   $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Réponse vide';

  //   return response()->json(['text' => $text]);
  // }
  
}
