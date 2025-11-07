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
use PhpParser\Node\Stmt\TryCatch;

class C_NetwoorkController extends Controller
{
 public function createAndPublishPostPictureFacebook(Request $request)
{
    $request->validate([
        'post' => 'required',
        'file' => 'required',
        'id_post' => 'nullable|integer',
    ]);

    $postData = $request->input('post');
    $fileData = $request->input('file');
$id_post = $request->input('id_post');

        $data = [
            "Post" => $postData,
            "File" => $fileData,
        ];

        // Envoi direct à Make pour publication Facebook
        $url = 'https://hook.eu2.make.com/umhsf8kaax437qklfxrf7oechd4hp3qk';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake"),
        ])->post($url, $data);
        
        if($id_post!= null){
        $request = new \Illuminate\Http\Request();
        $request->merge(['id_post' => $id_post]);
        $this->publishedPosts($request);  
        }
        return response()->json([
            'success' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->successful()
                ? 'Publication Facebook envoyée avec succès'
                : 'Erreur lors de l’envoi à Facebook',
            'response' => $response->json(),
        ]);
    }


  public function createAndPublishPostInstagramePicture(Request $request){
    $request->validate([
      'post' => 'required',
      'file' => 'required',
      'id_post' => 'nullable|integer',
    ]);

    $postData = $request->input('post');
    $filsData = $request->input('file');
$id_post = $request->input('id_post');
      $data = [
        "Post" => $postData,
        "File" => $filsData
      ];
       
      // Envoyer ces données directement à Make.com
      $url = 'https://hook.eu2.make.com/yf7x5kaq33anvdw12qrtykvxye4xvos9';
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'x-make-apikey' => env("KeyMake")
      ])->post($url, $data);
     
      if($id_post!= null){
      $request = new \Illuminate\Http\Request();
        $request->merge(['id_post' => $id_post]);
        $this->publishedPosts($request);  
      }
      return response()->json([
       
        'status' => $response->status(),
        'idPoste' => $response->body(),
      ]);
    
  }
  
public function createAndPublishPostPictureLinkeding(Request $request)
{
  $request->validate([
      'post' => 'required',
      'file' => 'required',
      'titre_post' => 'required',
      'id_post' => 'nullable|integer',
    ]);

    
    $FileData = $request->input('file');
    $Titre_PostData = $request->input('titre_post');
    $postData = $request->input('post');
    $id_post = $request->input('id_post');
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
if($id_post!= null){
          $request = new \Illuminate\Http\Request();
        $request->merge(['id_post' => $id_post]);
        $this->publishedPosts($request);  
} 
        return response()->json([
            'status' => $response->status(),
            'idPoste' => $response->body(),
            'data' => $data
            
        ]);
        

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
    public function validatedPosts(Request $request){
   $validated =  $request->validate([
        'id_post' => 'required|integer',
    ]);
      $idPostNetwork = $validated['id_post'];
     $userId = Auth::id() ?? $idPostNetwork;
        $post = Posts::where('IdpostNetwork', $userId)->first();
        if ($post) {
            $post->update([
                'isValidated' => true,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Post validé avec succès',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post non trouvé',
                'status' => 404,
            ], 404);
        }
    }
    public function publishedPosts(Request $request){
   $validated =  $request->validate([
        'id_post' => 'required|integer',
    ]);
      $idPostNetwork = $validated['id_post'];
     $userId = Auth::id() ?? $idPostNetwork;
        $post = Posts::where('IdpostNetwork', $userId)->first();
        if ($post) {
            $post->update([
                'isPublished' => true,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Post publié avec succès',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post non trouvé',
                'status' => 404,
            ], 404);
        }
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
            'network' => 'required|in:facebook,linkedin,instagram',
            'idPostNetwork' => 'required|string'
        ]);

      
        $url = $validated['url'];
        $titrePost = $validated['titre_post'];
        $postData = $validated['post'];
        $network = $validated['network'];
        $idPostNetwork = $validated['idPostNetwork'];
        $datePost = $validated['date'] ?? date('Y-m-d H:i:s');

        $userId = Auth::id() ?? $idUser;

     
        $post = Posts::create([
            'datePost' => $datePost,
            'idUser' => $userId,
            'isValidated' => false,
            'isPublished' => false,
            'network' => $network,
            'url' => $url,
            'titrePost' => $titrePost,
            'post' => $postData,
            'IdpostNetwork' => $idPostNetwork,
            'postLikeNetwork' => 0,
            'postCommentaireNetwork' => 0,
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

public function listerCommentairesAndLikeFacebook(Request $request)
{
    
    $validated = $request->validate([
        'id_post' => 'required|string',
    ]);

    $idPost = $validated['id_post'];
    $data = ["id_post" => $idPost];

    try {
        $urlLikes = 'https://hook.eu2.make.com/kdcygycquc2qslathnp9ukonrjweyuq2';
        $urlComments = 'https://hook.eu2.make.com/hm8ef29tbtu8zdgq7m26i8im9tqzxxbm';

       
        $responses = Http::pool(fn ($pool) => [
            $pool->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-make-apikey' => env("KeyMake"),
            ])->asJson()->post($urlLikes, $data),

            $pool->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-make-apikey' => env("KeyMake"),
            ])->asJson()->post($urlComments, $data),
        ]);

        $likesResponse = $responses[0];
        $commentsResponse = $responses[1];
        if (!$likesResponse->successful() || !$commentsResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données Facebook',
                'status' => [
                    'likes' => $likesResponse->status(),
                    'comments' => $commentsResponse->status(),
                ],
            ], 500);
        }

        $likesData = $likesResponse->json();
        $commentsData = $commentsResponse->json();

     
        if (!isset($likesData['array']) || !is_array($likesData['array'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format invalide pour les likes',
                'data' => $likesData,
            ], 500);
        }

        if (!isset($commentsData['array']) || !is_array($commentsData['array'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format invalide pour les commentaires',
                'data' => $commentsData,
            ], 500);
        }

     
        $likes = 0;
        foreach ($likesData['array'] as $likeItem) {
            if (($likeItem['id_post'] ?? null) === $idPost) {
                $likes = $likeItem['totalCount'] ?? 0;
                break;
            }
        }

        $comments = 0;
        foreach ($commentsData['array'] as $comment) {
            if (($comment['id_post'] ?? null) === $idPost) {
                $comments++;
            }
        }

        // 🔹 Mise à jour ou création du post
        $post = Posts::where('IdpostNetwork', $idPost)->first();

        if ($post) {
            $post->update([
                'postLikeNetwork' => $likes,
                'postCommentaireNetwork' => $comments,
            ]);
        } else {
            Posts::create([
                'datePost' => now(),
                'idUser' => 1,
                'isValidated' => false,
                'network' => 'facebook',
                'url' => '',
                'titrePost' => '',
                'post' => '',
                'IdpostNetwork' => $idPost,
                'postLikeNetwork' => $likes,
                'postCommentaireNetwork' => $comments,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Likes et commentaires mis à jour avec succès',
            'data' => [
                'id_post' => $idPost,
                'likes' => $likes,
                'comments' => $comments,
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage(),
        ], 500);
    }
}


public function listerCommentairesandLikeIstagram(){
  
  try {
    $url = 'https://hook.eu2.make.com/w9m5strj3ba5jn6mxsfj3clpgwtoekcc';
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'x-make-apikey' => env("KeyMake")
    ])->post($url);

    if($response->successful()) {
   $data = $response->json();

    // Vérifie que la clé "array" existe
    if (isset($data['array']) && is_array($data['array'])) {
        foreach ($data['array'] as $item) {
            $idPostInstagrame = $item['id'] ?? null;
            if ($idPostInstagrame === null) {
                continue;
            }

            $comment = $item['comments_count'] ?? 0;
            $likes = $item['like_count'] ?? 0;
            $nomPost = $item['caption'] ?? '';

            // Vérifie si le post existe déjà
            $post = Posts::where('IdpostNetwork', $idPostInstagrame)->first();

            if ($post) {
                // Met à jour le post existant
                $post->update([
                    'postCommentaireNetwork' => $comment,
                    'postLikeNetwork' => $likes,
                ]);
            } else {
                // Crée un nouveau post si besoin
                Posts::create([
                    'datePost' => now(),
                    'idUser' => 1, // ou récupère dynamiquement ton user
                    'isValidated' => false,
                    'network' => 'instagram',
                    'url' => $item['permalink'] ?? '',
                    'titrePost' => $nomPost,
                    'post' => $nomPost,
                    'IdpostNetwork' => $idPostInstagrame,
                    'postLikeNetwork' => $likes,
                    'postCommentaireNetwork' => $comment,
                ]);
            }
        }

    } else {
        logger()->warning('La réponse de Make ne contient pas de clé "array"', $data);
    }
    return response()->json([
        'success' => true,
        'message' => 'Données Instagram mises à jour avec succès',
        'status' => 200,
    ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des données',
            'status' => $response->status(),
        ], $response->status());
    }
   }catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ], 500);
}
}

public function listerCommentairesandLikeLinkeding(Request $request)
{
    try {
        $idLinkedin = $request->input('id_post');

        if (!$idLinkedin) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre "id_post" manquant dans le body.',
            ], 400);
        }

        $url = 'https://hook.eu2.make.com/3imdcn8neofzgaeqnbrcstxd3ogayxew';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake"),
        ])->post($url, [
            'id_post' => $idLinkedin,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            logger()->info('Réponse Make LinkedIn', $data);

            if (isset($data['array']) && is_array($data['array'])) {
                foreach ($data['array'] as $item) {
                    $idPostLinkedin = $item['share'] ?? null;
                    if ($idPostLinkedin === null) {
                        continue;
                    }

                    // On va chercher les stats dans le sous-tableau totalShareStatistics
                    $stats = $item['totalShareStatistics'] ?? [];
                    $likes = $stats['likeCount'] ?? 0;
                    $comment = $stats['commentCount'] ?? 0;


                    // Vérifie si le post existe déjà
                    $post = Posts::where('IdpostNetwork', $idPostLinkedin)->first();

                    if ($post) {
                        $post->update([
                            'postCommentaireNetwork' => $comment,
                            'postLikeNetwork' => $likes,

                        ]);
                    } else {
                        Posts::create([
                            'datePost' => now(),
                            'idUser' => 1, 
                            'isValidated' => false,
                            'network' => 'linkedin',
                            'url' => '', 
                            'titrePost' => 'Post LinkedIn ' . $idPostLinkedin,
                            'post' => 'Post LinkedIn ' . $idPostLinkedin,
                            'IdpostNetwork' => $idPostLinkedin,
                            'postLikeNetwork' => $likes,
                            'postCommentaireNetwork' => $comment,
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Données LinkedIn mises à jour avec succès',
                    'status' => 200,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Format de réponse Make invalide (clé "array" manquante)',
                'data' => $data,
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des données LinkedIn',
            'status' => $response->status(),
        ], $response->status());

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage(),
        ], 500);
    }
}

public function ListeCommentaireAndLikeNetwork()
{
    try {
        // 🔹 Récupère tous les posts
        $posts = Posts::all();

        if ($posts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun post trouvé dans la base de données',
            ], 404);
        }

        $results = [];

        // 🔹 Boucle sur chaque post
        foreach ($posts as $post) {
            $idPost = $post->IdpostNetwork;
            $network = strtolower($post->network);

            // On crée une Request simulée pour passer l’idPost
            $request = new \Illuminate\Http\Request();
            $request->merge(['id_post' => $idPost]);

            switch ($network) {
                case 'facebook':
                    $response = $this->listerCommentairesAndLikeFacebook($request);
                    break;

                case 'instagram':
                    $response = $this->listerCommentairesandLikeIstagram();
                    break;

                case 'linkedin':
                    $response = $this->listerCommentairesandLikeLinkeding($request);
                    break;

                default:
                    $response = response()->json([
                        'success' => false,
                        'message' => "Réseau inconnu pour le post $idPost",
                    ]);
                    break;
            }

            $results[] = [
                'id_post' => $idPost,
                'network' => $network,
                'result' => json_decode($response->getContent(), true),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Mise à jour des commentaires et likes effectuée pour tous les réseaux',
            'results' => $results,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage(),
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
