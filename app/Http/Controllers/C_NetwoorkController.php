<?php

namespace App\Http\Controllers;

use App\Http\Controllers\C_UserController as ControllersC_UserController;
use App\Http\Controllers\Controller;
use App\Models\Posts;
use App\Models\Abonnements;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
class C_NetwoorkController extends Controller
{
  public function createAndPublishPostFacebook(Request $request)
  {
    

    $postData = $request->input('post');

    // $userId = Auth::id() ?? 1;

    if ($request->input('date') === null && $request->input('now') === true) {
      $date = new DateTime();
      $date = $date->format('Y-m-d-H:i:s');
    } else {
      $date = $request->input('date');
    }

    // Posts::create([
    //   "datePost" => $date,
    //   "idUser" => $userId,
    //   "idPieceJointe" => 0,
    //   "post" => $postData
    // ]);


    if ($request->input('date') === null && $request->input('now') === true) {

      $data = [
        "post" => $postData
      ];

      // Envoyer ces données directement à Make.com
      $url = 'https://hook.eu2.make.com/umhsf8kaax437qklfxrf7oechd4hp3qk';
      $response = Http::withHeaders([
        'Content-Type' => 'application/json', // Utiliser JSON pourrait être plus simple
        'Accept' => 'application/json',
      ])->post($url, $data);

      return response()->json([
        'status' => $response->status(),
        'body' => $response->body(),
      ]);
    } else {
      return response()->json([
        'status' => 200,
        'message' => 'Post correctement plannifié',
      ]);
    }
  }// 3T-QSYRS-FUfssc
  public function createAndPublishPostInstagramePicture(Request $request){
    $request->validate([
      'post' => 'required',
      'file' => 'required',
      'date' => 'required',
      'now' => 'required'
    ]);

    $postData = $request->input('post');
    $filsData = $request->input('file');

    // $userId = Auth::id() ?? 1;

    if ($request->input('date') === null && $request->input('now') == true) {
      $date = new DateTime();
      $date = $date->format('Y-m-d-H:i:s');
    } else {
      $date = $request->input('date');
    }

    // Posts::create([
    //   "datePost" => $date,
    //   "idUser" => $userId,
    //   "idPieceJointe" => 0,
    //   "post" => $postData,
    // ]);

    if ( $request->input('now') == true) {  // ajouter $request->input('date') === null &&

      $data = [
        "Post" => $postData,
        "File" => $filsData
      ];
       
      // Envoyer ces données directement à Make.com
      $url = 'https://hook.eu2.make.com/gj0upuefvv5a4u23c6jhjmukkzju2md2';
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'x-make-apikey' => env("KeyMake")
      ])->post($url, $data);

      return response()->json([
        'status' => $response->status(),
        'body' => $response->body(),
      ]);
    } else {
      return response()->json([
        'status' => 200,
        'message' => 'Post correctement plannifié',
      ]);
    }
  }
  public function createAndPublishPostLinkeding(Request $request)
{
  $request->validate([
      'post' => 'required',
      'date' => 'required',
      'now' => 'required'
      
    ]);
    $postData = $request->input('post');
    // $userId = Auth::id() ?? 1;

    // Gestion de la date
    if ($request->input('date') === null && $request->input('now') === true) {
        $date = now()->format('Y-m-d-H:i:s');
    } else {
        $date = $request->input('date');
    }

    // Enregistrer le post
    // Posts::create([
    //     "datePost" => $date,
    //     "idUser" => $userId,
    //     "idPieceJointe" => 0,
    //     "post" => $postData
    // ]);

    // Si on doit publier maintenant
    if ($request->input('now') === true) {
        $data = [
            "Post" => $postData
        ];

        $url = 'https://hook.eu2.make.com/8wla3eo601n8ii146jwuoezmja07vs3r';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json', 
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake")
        ])->post($url, $data);

        return response()->json([
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Post correctement plannifié',
    ]);
}
public function createAndPublishPostPictureLinkeding(Request $request)
{
  $request->validate([
      'post' => 'required',
      'file' => 'required',
      'titre_post' => 'required',
      'date' => 'required',
      'now' => 'required'
    ]);

    
    $FileData = $request->input('file');
    $Titre_PostData = $request->input('titre_post');
    $postData = $request->input('post');
    // $userId = Auth::id() ?? 1;

    // Gestion de la date
    if ($request->input('date') === null && $request->input('now') == true) {
        $date = now()->format('Y-m-d-H:i:s');
    } else {
        $date = $request->input('date');
    }


    // Enregistrer le post
    // Posts::create([
    //     "datePost" => $date,
    //     "idUser" => $userId,
    //     "post" => $postData
    // ]);

    // Si on doit publier maintenant
    if ($request->input('now') == "true") {
       $data = [

            "Titre_Post" => $Titre_PostData,
            "File" => $FileData,
            "Post" => $postData,
        ];
        $url = 'https://hook.eu2.make.com/hifthnguoljpbwu3hfbripma447f2f8k';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json', 
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake")
        ])->post($url, $data);

        return response()->json([
            'status' => $response->status(),
            'body' => $response->body(),
            'data' => $data
            
        ]);
        
    }

    return response()->json([
        'status' => 200,
        'message' => 'Post correctement plannifié ',
    ]);
}

 public function ListerPosts($id)
    {
        $userId = Auth::id() ?? $id;
    
        $posts = Posts::where('idUser', $userId)->get();
       
        if ($posts->count() > 0) {
            return response()->json([
                'tabListe' => $posts,
                'status' => 200,
            ], 200);
        }

        return response()->json([
            'message' => 'Aucun post trouvé pour cet utilisateur.',
            'status' => 404,
        ], 404);
    }
public function addPosts(Request $request, $idUser)
{
    try {
        
        if (!is_numeric($idUser)) {
            return response()->json([
                'success' => false,
                'message' => 'ID utilisateur invalide'
            ], 400);
        }

        $validated = $request->validate([
            'post' => 'required|string',
            'url' => 'required|url',
            'titre_post' => 'required|string',
            'date' => 'required|date', 
            'network' => 'required|in:facebook,linkeding,instagrame',
        ]);

      
        $url = $validated['url'];
        $titrePost = $validated['titre_post'];
        $postData = $validated['post'];
        $network = $validated['network'];
        $datePost = $validated['date'] ?? date('Y-m-d H:i:s');

        $userId = Auth::id() ?? $idUser;

     
        $post = Posts::create([
            'datePost' => $datePost,
            'idUser' => $userId,
            'isValidated' => false,
            'network' => $network,
            'url' => $url,
            'titrePost' => $titrePost,
            'post' => $postData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post ajouté avec succès',
            'user' => $userId,
            'tabListe' => [$post],
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage()
        ], 500);
    }
}


 
    // // Vérifie l'abonnement de l'utilisateur
    // $abonnement = \App\Http\Controllers\C_UserController::abonnementUser($userId);
    // if ($abonnement['error']) {
    //     return response()->json([
    //         'message' => $abonnement['message'],
    //         'status' => 404,
    //     ], 404);
    // }

    // // Définit les limites selon le type d'abonnement
    // switch ($abonnement['AbonementType']) {
    //     case "isFree":
    //         $limiteText = 10;
    //         $limitevisuel = 2;
    //         break;
    //     case "isPremium":
    //         $limiteText = 15;
    //         $limitevisuel = 5;
    //         break;
    //     case "isProfessionnel":
    //         $limiteText = 20;
    //         $limitevisuel = 10;
    //         break;
    //     default:
    //         return response()->json([
    //             'message' => 'Type d\'abonnement inconnu',
    //             'status' => 400,
    //         ], 400);
    // }

    // $listePosts = [];
    // $iaController = new \App\Http\Controllers\C_IAController();

    // $datesNetworks = [];
    // $path = storage_path('app/private/fille/data_date.txt');

    // if (!file_exists($path)) {
    //     return response()->json([
    //         'message' => "Fichier de dates introuvable: $path",
    //         'status' => 500,
    //     ], 500);
    // }
    // $lines = explode("\n", file_get_contents($path));
    // foreach ($lines as $line) {
    //     $line = trim($line);
    //     if (empty($line)) continue;
    //     $parts = explode(',', $line);
    //     if (count($parts) == 2) {
    //         $datesNetworks[] = [
    //             'date' => trim($parts[0]),
    //             'network' => strtolower(trim($parts[1]))
    //         ];
    //     }
    // }
    // if (count($datesNetworks) === 0) {
    //     return response()->json([
    //         'message' => "Pas de dates ou réseaux valides dans le fichier.",
    //         'status' => 500,
    //     ], 500);
    // }

    
    // $nbPostsAGenerer = $limiteText;

   
    // for ($i = 0; $i < $nbPostsAGenerer; $i++) {
    //     $index = $i % count($datesNetworks); 
    //     $dayFromData = str_pad($datesNetworks[$index]['date'], 2, '0', STR_PAD_LEFT);
    //     $network = $datesNetworks[$index]['network'];
        
    //     // Date du post
    //     $targetDateString = date('Y-m') . '-' . $dayFromData;
    //     $targetDateTime = \Carbon\Carbon::parse($targetDateString);
    //     if ($targetDateTime->isPast() && $targetDateTime->format('Y-m-d') <= date('Y-m-d')) {
    //         $targetDateTime->addMonthNoOverflow();
    //     }
    //     $datePost = $targetDateTime->format('Y-m-d') . ' 00:00:01';

       
    //     $urlpicture = null;
    //     if ($i < $limitevisuel) {
    //         $prompt = "Créer une image professionnelle pour un post $network sur le thème de l'intelligence artificielle dans le domaine de la finance, avec des couleurs vertes et blanches, style moderne et épuré, format carré";
    //         $request->merge(['prompt' => $prompt]);
    //         $imageResponse = $iaController->generatPictureGPT($request)->getData(true);
    //         $urlpicture = $imageResponse['image_url'] ?? null;
    //     }

    //     $prompt = "Rédige un texte professionnel pour un post $network sur l'IA et la finance (style moderne, accrocheur).";
    //     $request->merge(['prompt' => $prompt]);
    //     $textResponse = $iaController->generatpromptgemini($request)->getData(true);
    //     $postData = $textResponse['text'] ?? "Texte non généré";
 
    //     // Création du post
    //     $post = Posts::create([
    //         "datePost" => $datePost,
    //         "idUser" => $userId,
    //         "isValidated" => false,
    //         "network" => $network,
    //         "url" => $urlpicture,
    //         "titrePost" => "Post " . ($i + 1) . " - " . ucfirst($network),
    //         "post" => $postData
    //     ]);

    //     $listePosts[] = $post;
        
    // }

    // return response()->json([
    //     'message' => 'Posts générés automatiquement',
    //     'user' => $userId,
    //     'tabListe' => $listePosts,
    //     'status' => 200,
    // ], 200);



}
