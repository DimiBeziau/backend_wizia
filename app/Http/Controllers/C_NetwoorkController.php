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
/**
 * @OA\Post(
 *     path="/post",
 *     summary="Créer ou publier un post sur un réseau social",
 *     tags={"Post"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post","url","network","idUser","isValidated"},
 *             @OA\Property(property="post", type="string", example="Mon texte de post"),
 *             @OA\Property(property="titrePost", type="string", example="Titre optionnel"),
 *             @OA\Property(property="url", type="string", example="https://images.unsplash.com/photo-12345"),
 *             @OA\Property(property="id_post", type="integer", nullable=true, example=1),
 *             @OA\Property(property="now", type="boolean", example=true),
 *             @OA\Property(property="datePost", type="string", format="date", example="2025-08-17"),
 *             @OA\Property(property="network", type="string", enum={"facebook","instagram","linkedin"}, example="facebook"),
 *             @OA\Property(property="idUser", type="integer", example=1),
 *             @OA\Property(property="isValidated", type="integer", enum={0,1}, example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Post traité avec succès"),
 *     @OA\Response(response=400, description="Réseau social non supporté ou validation échouée"),
 *     @OA\Response(response=500, description="Erreur interne serveur")
 * )
 */

    public function createPublishPost(Request $request)
{

    $request->validate([
        'post' => 'required|string|max:5000',
        'titrePost' => 'nullable|string',
        'url' => 'required|string',
        'id_post' => 'nullable|integer', 
        'datePost' => 'nullable|date',
        'network' => 'required|string|in:facebook,instagram,linkedin',
        'idUser' => 'required|integer',
        'now' => 'nullable|boolean',
        'isValidated' => 'required|integer|in:0,1',
    ]);

    switch ($request->input('network')) {
        case 'facebook':
            return $this->createAndPublishPostPictureFacebook($request);

        case 'instagram':
            return $this->createAndPublishPostInstagramePicture($request);

        case 'linkedin':
            return $this->createAndPublishPostPictureLinkeding($request);

        default:
            return response()->json([
                'success' => false,
                'message' => 'Réseau social non supporté',
            ], 400);
    }
}

 /**
 * @OA\Post(
 *     path="/post/Facebook",
 *     summary="Publier ou planifier un post image sur Facebook",
 *     tags={"Post"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post","url","idUser","isValidated"},
 *             @OA\Property(property="post", type="string", example="Mon texte de post Facebook"),
 *             @OA\Property(property="titrePost", type="string", example="Titre optionnel"),
 *             @OA\Property(property="url", type="string", example="https://images.unsplash.com/photo-12345"),
 *             @OA\Property(property="id_post", type="integer", nullable=true, example=1),
 *             @OA\Property(property="now", type="boolean", example=true),
 *             @OA\Property(property="datePost", type="string", format="date", example="2025-08-17"),
 *             @OA\Property(property="idUser", type="integer", example=1),
 *             @OA\Property(property="isValidated", type="integer", enum={0,1}, example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Publication envoyée à Facebook"),
 *     @OA\Response(response=400, description="Données invalides"),
 *     @OA\Response(response=401, description="Erreur Make.com (clé invalide)"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */

public function createAndPublishPostPictureFacebook(Request $request)
{
    $titrePost = $request->input('titrePost');
    $postContent = $request->input('post');
    $url = $request->input('url');
    $idPostBDD = $request->input('id_post');
    $datePost = $request->input('datePost');
    $sendNow = $request->boolean('now');
    $userId = $request->input('idUser');
    $isValidated = $request->input('isValidated');

    $fullPost = trim(($titrePost ? $titrePost . "\n" : "") . $postContent);

    if ($sendNow) {
        $data = [
            "Post" => $fullPost,
            "File" => $url,
        ];

        $urlMake = 'https://hook.eu2.make.com/umhsf8kaax437qklfxrf7oechd4hp3qk';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake")
        ])->post($urlMake, $data);
/** @var \Illuminate\Http\Client\Response $response */ // Aide l'IDE à reconnaître les méthodes
        $idPostNetwork = $response->body();

        if ($idPostBDD !== null) {
            $reqUpdate = new Request([
                "id" => $idPostBDD,
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "facebook",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->updatePosts($reqUpdate, $userId);
        } else {
            $reqAdd = new Request([
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "facebook",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->addPosts($reqAdd, $userId);
        }

        // Récupérer l'ID depuis la réponse JsonResponse
        $postData = $postResponse->getData(true);
        $postId = $postData['id'] ?? null;

        
        $this->publishedPosts($postId);

        return response()->json([
            "success" => $response->successful(),
            "status" => $response->status(),
            "message" => $response->successful()
                ? "Publication Facebook envoyée & post mis à jour"
                : "Erreur lors de l’envoi à Facebook",
            "idPostNetwork" => $idPostNetwork,
            "makeResponse" => $response->json(),
        ]);
    }

    $reqAddLater = new Request([
        "post" => $postContent,
        "url" => $url,
        "titre_post" => $titrePost ?: "Sans titre",
        "date" => $datePost ?? now(),
        "network" => "facebook",
        "idPostNetwork" => "",
        "isValidated" => $isValidated
    ]);

    if ($idPostBDD) {
        return $this->updatePosts($reqAddLater, $userId);
    } else {
        return $this->addPosts($reqAddLater, $userId);
    }
}


/**
 * @OA\Post(
 *     path="/post/Instagrame",
 *     summary="Publier ou planifier un post image sur Instagram",
 *     tags={"Post"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post","url","idUser","isValidated"},
 *             @OA\Property(property="post", type="string", example="Mon post Instagram"),
 *             @OA\Property(property="titrePost", type="string", example="Titre Instagram optionnel"),
 *             @OA\Property(property="url", type="string", example="data:image/jpeg;base64,..."),
 *             @OA\Property(property="id_post", type="integer", nullable=true, example=12),
 *             @OA\Property(property="now", type="boolean", example=true),
 *             @OA\Property(property="datePost", type="string", format="date", example="2025-09-10"),
 *             @OA\Property(property="idUser", type="integer", example=1),
 *             @OA\Property(property="isValidated", type="integer", enum={0,1}, example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Publication envoyée à Instagram"),
 *     @OA\Response(response=400, description="Données manquantes ou invalides"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */


  public function createAndPublishPostInstagramePicture(Request $request){

    $titrePost = $request->input('titrePost');
    $postContent = $request->input('post');
    $url = $request->input('url');
    $idPostBDD = $request->input('id_post');
    $datePost = $request->input('datePost');
    $sendNow = $request->boolean('now');
    $userId = $request->input('idUser');
    $isValidated = $request->input('isValidated');

    $fullPost = trim(($titrePost ? $titrePost . "\n" : "") . $postContent);

    if ($sendNow) {
        $data = [
            "Post" => $fullPost,
            "File" => $url,
        ];

        $urlMake = 'https://hook.eu2.make.com/yf7x5kaq33anvdw12qrtykvxye4xvos9';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake")
        ])->post($urlMake, $data);
/** @var \Illuminate\Http\Client\Response $response */ // Aide l'IDE à reconnaître les méthodes
        $idPostNetwork = $response->body();

        if ($idPostBDD !== null) {
            $reqUpdate = new Request([
                "id" => $idPostBDD,
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "instagram",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->updatePosts($reqUpdate, $userId);
        } else {
            $reqAdd = new Request([
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "instagram",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->addPosts($reqAdd, $userId);
        }

        // Récupérer l'ID depuis la réponse JsonResponse
        $postData = $postResponse->getData(true);
        $postId = $postData['id'] ?? null;


        $this->publishedPosts($postId);

        return response()->json([
            "success" => $response->successful(),
            "status" => $response->status(),
            "message" => $response->successful()
                ? "Publication instagram envoyée & post mis à jour"
                : "Erreur lors de l’envoi à instagram",
            "idPostNetwork" => $idPostNetwork,
            "makeResponse" => $response->json(),
        ]);
    }

    $reqAddLater = new Request([
        "post" => $postContent,
        "url" => $url,
        "titre_post" => $titrePost ?: "Sans titre",
        "date" => $datePost ?? now(),
        "network" => "instagram",
        "idPostNetwork" => "",
        "isValidated" => $isValidated
    ]);

    if ($idPostBDD) {
        return $this->updatePosts($reqAddLater, $userId);
    } else {
        return $this->addPosts($reqAddLater, $userId);
    }

//     $request->validate([
//       'post' => 'required',
//       'file' => 'required',
//       'titrePost' => 'nullable|string',
//       'id_post' => 'nullable|integer',
//       'sendNow' => 'nullable|boolean',
//     'datePost' => 'nullable|date',
//       'network' => 'required|string|in:facebook,instagram,linkedin',
//     ]);
    

//     $postData = $request->input('post');
//     $filsData = $request->input('file');
// $id_post = $request->input('id_post');
//       $data = [
//         "Post" => $postData,
//         "File" => $filsData
//       ];
       
//       // Envoyer ces données directement à Make.com
//       $url = 'https://hook.eu2.make.com/yf7x5kaq33anvdw12qrtykvxye4xvos9';
//       $response = Http::withHeaders([
//         'Content-Type' => 'application/json',
//         'Accept' => 'application/json',
//         'x-make-apikey' => env("KeyMake")
//       ])->post($url, $data);
     
//       if($id_post!= null){
//       $request = new \Illuminate\Http\Request();
//         $request->merge(['id_post' => $id_post]);
//         $this->publishedPosts($request);
//       }
//       return response()->json([
       
//         'status' => $response->status(),
//         'idPoste' => $response->body(),
//       ]);
    
  }
 /**
 * @OA\Post(
 *     path="/post/Linkeding",
 *     summary="Publier ou planifier un post image sur LinkedIn",
 *     tags={"Post"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post","url","titrePost","idUser","isValidated"},
 *             @OA\Property(property="titrePost", type="string", example="Titre LinkedIn"),
 *             @OA\Property(property="post", type="string", example="Contenu du post LinkedIn"),
 *             @OA\Property(property="url", type="string", example="data:image/jpeg;base64,..."),
 *             @OA\Property(property="id_post", type="integer", nullable=true, example=34),
 *             @OA\Property(property="now", type="boolean", example=true),
 *             @OA\Property(property="datePost", type="string", format="date", example="2025-10-02"),
 *             @OA\Property(property="idUser", type="integer", example=1),
 *             @OA\Property(property="isValidated", type="integer", enum={0,1}, example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Publication LinkedIn envoyée"),
 *     @OA\Response(response=400, description="Données invalides"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */


public function createAndPublishPostPictureLinkeding(Request $request)
{
    $titrePost = $request->input('titrePost');
    $postContent = $request->input('post');
    $url = $request->input('url');
    $idPostBDD = $request->input('id_post');
    $datePost = $request->input('datePost');
    $sendNow = $request->boolean('now');
    $userId = $request->input('idUser');
    $isValidated = $request->input('isValidated');

    

    if ($sendNow) {
        $data = [
            "Titre_Post" => $titrePost,
            "Post" => $postContent,
            "File" => $url,
        ];

        $urlMake = 'https://hook.eu2.make.com/hifthnguoljpbwu3hfbripma447f2f8k';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyMake")
        ])->post($urlMake, $data);
/** @var \Illuminate\Http\Client\Response $response */ // Aide l'IDE à reconnaître les méthodes
        $idPostNetwork = $response->body();

        if ($idPostBDD !== null) {
            $reqUpdate = new Request([
                "id" => $idPostBDD,
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "linkedin",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->updatePosts($reqUpdate, $userId);
        } else {
            $reqAdd = new Request([
                "post" => $postContent,
                "url" => $url,
                "titre_post" => $titrePost ?: "Sans titre",
                "date" => $datePost,
                "network" => "linkedin",
                "idPostNetwork" => $idPostNetwork,
                "isValidated" => $isValidated
            ]);

            $postResponse = $this->addPosts($reqAdd, $userId);
        }

        // Récupérer l'ID depuis la réponse JsonResponse
        $postData = $postResponse->getData(true);
        $postId = $postData['id'] ?? null;

        
        $this->publishedPosts($postId);

        return response()->json([
            "success" => $response->successful(),
            "status" => $response->status(),
            "message" => $response->successful()
                ? "Publication linkedin envoyée & post mis à jour"
                : "Erreur lors de l’envoi à linkedin",
            "idPostNetwork" => $idPostNetwork,
            "makeResponse" => $response->json(),
        ]);
    }

    $reqAddLater = new Request([
        "post" => $postContent,
        "url" => $url,
        "titre_post" => $titrePost ?: "Sans titre",
        "date" => $datePost ?? now(),
        "network" => "linkedin",
        "idPostNetwork" => "",
        "isValidated" => $isValidated
    ]);

    if ($idPostBDD) {
        return $this->updatePosts($reqAddLater, $userId);
    } else {
        return $this->addPosts($reqAddLater, $userId);
    }

//   $request->validate([
//       'post' => 'required',
//       'file' => 'required',
//       'titrePost' => 'required',
//       'id_post' => 'nullable|integer',
//     ]);

    
//     $FileData = $request->input('file');
//     $Titre_PostData = $request->input('titrePost');
//     $postData = $request->input('post');
//     $id_post = $request->input('id_post');
//        $data = [

//             "Titre_Post" => $Titre_PostData,
//             "File" => $FileData,
//             "Post" => $postData,
//         ];
//         $url = 'https://hook.eu2.make.com/hifthnguoljpbwu3hfbripma447f2f8k';
//         $response = Http::withHeaders([
//             'Content-Type' => 'application/json', 
//             'Accept' => 'application/json',
//             'x-make-apikey' => env("KeyMake")
//         ])->post($url, $data);
// if($id_post!= null){
//           $request = new \Illuminate\Http\Request();
//         $request->merge(['id_post' => $id_post]);
//         $this->publishedPosts($request);  
// } 
//         return response()->json([
//             'status' => $response->status(),
//             'idPoste' => $response->body(),
//             'data' => $data
            
//         ]);
        

//     return response()->json([
//         'status' => 200,
//         'message' => 'Post correctement plannifié ',
//     ]);
}
/**
 * @OA\Get(
 *     path="/post/ListePosts/{id}",
 *     summary="Lister toutes les publications d'un utilisateur",
 *     tags={"Post"},
 *     @OA\Parameter(
 *         name="id_User",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         example=3
 *     ),
 *     @OA\Response(response=200, description="Liste trouvée"),
 *     @OA\Response(response=404, description="Aucun post trouvé")
 * )
 */

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
    /**
 * @OA\Post(
 *     path="/post/SearchPost/{idPost}",
 *     summary="Récupérer un post par ID",
 *     tags={"Post"},
 *     @OA\Parameter(
 *         name="idPost",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         example=10
 *     ),
 *     @OA\Response(response=200, description="Post trouvé"),
 *     @OA\Response(response=404, description="Post non trouvé")
 * )
 */

    public function SearchPost($idPost)
    {
        $posts = Posts::where('id', $idPost)->get();
       
        if ($posts->count() > 0) {
            return response()->json([
                'Post' => $posts,
                'status' => 200,
            ], 200);
        }

        return response()->json([
            'message' => 'Aucun post trouvé pour cet utilisateur.',
            'status' => 404,
        ], 404);
    }
    public function validatedPosts(Request $request){

 $validated = $request->validate([
        'id_post' => 'required|integer',
    ]);

    $post = Posts::find($validated['id_post']);

    if (!$post) {
        return response()->json([
            'success' => false,
            'message' => 'Post non trouvé',
            'status' => 404,
        ], 404);
    }

    $post->update(['isValidated' => true]);

    return response()->json([
        'success' => true,
        'message' => 'Post publié avec succès',
        'status' => 200,
    ], 200);
    }
    /**
 * @OA\Post(
 *     path="/post/published",
 *     summary="Marquer un post comme publié",
 *     tags={"Post"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id_post"},
 *             @OA\Property(property="id_post", type="string", example="45")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Post marqué comme publié"),
 *     @OA\Response(response=400, description="ID réseau manquant dans le post"),
 *     @OA\Response(response=404, description="Post non trouvé"),
 *     @OA\Response(response=500, description="Erreur server")
 * )
 */

   public function publishedPosts($postId)
    {
    $post = Posts::find($postId);

    if (!$post) { 
        return response()->json([
            'success' => false,
            'message' => 'Post non trouvé',
            'status' => 404,
        ], 404);
    }

    if (empty($post->IdpostNetwork)) {
        return response()->json([
            'success' => false,
            'message' => "Le post n'a pas d'ID réseau associé.",
            'status' => 400,
        ], 400);
    }

    $post->update(['isPublished' => true]);

    return response()->json([
        'success' => true,
        'message' => 'Post publié avec succès',
        'status' => 200,
    ], 200);
}

    /**
 * @OA\Post(
 *     path="/post/addPosts/{idUser}",
 *     summary="Ajouter un post à la base",
 *     tags={"Post"},
 *     @OA\Parameter(
 *         name="idUser",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         example=5
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post","url","titre_post","date","network","idPostNetwork"},
 *             @OA\Property(property="post", type="string", example="Super post Facebook"),
 *             @OA\Property(property="url", type="string", example="https://facebook.com/post/123"),
 *             @OA\Property(property="titre_post", type="string", example="Mon titre"),
 *             @OA\Property(property="date", type="string", example="2025-03-10 14:00:00"),
 *             @OA\Property(property="network", type="string", example="facebook"),
 *             @OA\Property(property="idPostNetwork", type="string", example="123456789")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Post ajouté"),
 *     @OA\Response(response=400, description="Erreur validation"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

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
            'idPostNetwork' => 'nullable|string',
            'isValidated' => 'required|integer|in:0,1',
        ]);

      
        $url = $validated['url'];
        $titrePost = $validated['titre_post'];
        $postData = $validated['post'];
        $network = $validated['network'];
        $idPostNetwork = $validated['idPostNetwork'] ?? '';
        $datePost = $validated['date'] ?? date('Y-m-d H:i:s');

        $userId = Auth::id() ?? $idUser;

     
        $post = Posts::create([
            'datePost' => $datePost,
            'idUser' => $userId,
            'isValidated' => $validated['isValidated'],
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
            'id' => $post->id,
            'post' => $post,
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Put(
 *     path="/posts/update/{idUser}",
 *     summary="Modifier un post existant",
 *     tags={"Post"},
 *     @OA\Parameter(
 *         name="idUser",
 *         in="path",
 *         required=true,
 *         description="ID de l'utilisateur",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"post", "url", "titre_post", "date", "network"},
 *             @OA\Property(property="post", type="string", example="Nouveau contenu du post"),
 *             @OA\Property(property="url", type="string", format="url", example="https://image.com/photo.jpg"),
 *             @OA\Property(property="titre_post", type="string", example="Titre modifié"),
 *             @OA\Property(property="date", type="string", format="date", example="2025-10-12"),
 *             @OA\Property(property="network", type="string", enum={"facebook","instagram","linkedin"}, example="facebook"),
 *             @OA\Property(property="idPostNetwork", type="string", nullable=true, example="FB_123456")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Post modifié avec succès"),
 *     @OA\Response(response=400, description="Erreur de validation ou ID utilisateur invalide"),
 *     @OA\Response(response=404, description="Post introuvable"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

public function updatePosts(Request $request, $idUser)
{
    try {

        if (!is_numeric($idUser)) {
            return response()->json([
                'success' => false,
                'message' => 'ID utilisateur invalide'
            ], 400);
        }

        $validated = $request->validate([
            'id' => 'required|integer',  
            'post' => 'required|string',
            'url' => 'required|url',
            'titre_post' => 'required|string',
            'date' => 'required|date',
            'network' => 'required|in:facebook,linkedin,instagram',
            'idPostNetwork' => 'nullable|string',
            'isValidated' => 'required|integer|in:0,1',
        ]);

        $postId = $validated['id'];

        $post = Posts::where('id', $postId)->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => "Post introuvable"
            ], 404);
        }

        $post->update([
            'datePost' => $validated['date'] ?? date('Y-m-d H:i:s'),
            'idUser' => $idUser,
            'isValidated' => $validated['isValidated'],
            'isPublished' => false,
            'network' => $validated['network'],
            'url' => $validated['url'],
            'titrePost' => $validated['titre_post'],
            'post' => $validated['post'],
            'IdpostNetwork' => $validated['idPostNetwork'] ?? "",
            'postLikeNetwork' => 0,
            'postCommentaireNetwork' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post modifié avec succès',
            'user' => $idUser,
            'id' => $post->id,
             'post' => $post,
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage()
        ], 500);
    }
}

/**
 * @OA@Post(
 *     path="/post/listerCommentairesandLikeFacebook",
 *     summary="Lister likes & commentaires Facebook",
 *     tags={"Post"},
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */

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

/**
 * @OA\Post(
 *     path="/post/listerCommentairesandLikeInstagram",
 *     summary="Lister likes & commentaires Instagram",
 *     tags={"Post"},
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */

public function listerCommentairesandLikeIstagram(){
  
  try {
    $url = 'https://hook.eu2.make.com/w9m5strj3ba5jn6mxsfj3clpgwtoekcc';
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'x-make-apikey' => env("KeyMake")
    ])->post($url);
/** @var \Illuminate\Http\Client\Response $response */ // Aide l'IDE à reconnaître les méthodes
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
/**
 * @OA\Post(
 *     path="/post/listerCommentairesandLikeLinkeding",
 *     summary="Lister likes & commentaires LinkedIn",
 *     tags={"Post"},
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */

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
/** @var \Illuminate\Http\Client\Response $response */ // Aide l'IDE à reconnaître les méthodes
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
/**
 * @OA\Post(
 *     path="/post/listerCommentairesandLike",
 *     summary="Mettre à jour likes et commentaires sur tous les réseaux",
 *     tags={"Post"},
 *     @OA\Response(response=200, description="Données mises à jour"),
 *     @OA\Response(response=500, description="Erreur interne")
 * )
 */

public function ListeCommentaireAndLikeNetwork()
{
    try {
        
        $posts = Posts::all();

        if ($posts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun post trouvé dans la base de données',
            ], 404);
        }

        $results = [];

      
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
/**
 * @OA\Post(
 *     path="/post/UploadPictureNetwork",
 *     summary="Upload d'une image pour un post réseau",
 *     tags={"Post"},
 *     @OA\Response(response=200, description="Image uploadée avec succès"),
 *     @OA\Response(response=500, description="Erreur lors de l'upload de l'image")
 * )
 */
public function UploadPictureNetwork(Request $request)
{
     try {
      $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif',

      ]);
      $image = $request->file('file');
      $imagePath = $image->store('NetworkPicture', 'public');

      return response()->json([
        'message' => 'Image uploadée et enregistrée avec succès',
        'path' => env("APP_URL") . '/storage/' . $imagePath
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Erreur lors de l\'ajout de l\'image',
        'error' => $e->getMessage()
      ], 500);
    }

}
/**
 * @OA\Post(
 *     path="/post/generer-automatiquement",
 *     summary="Générer et publier automatiquement les posts planifiés",
 *     description="Recherche tous les posts validés, non publiés, dont la date/heure de publication est passée, puis les publie sur le réseau social associé.",
 *     tags={"Post"},
 *     @OA\Response(
 *         response=200,
 *         description="Traitement effectué : posts publiés automatiquement ou aucun post à traiter.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Tous les posts validés ont été traités et publiés automatiquement.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur serveur : détails de l’erreur"),
 *             @OA\Property(property="status", type="integer", example=500)
 *         )
 *     )
 * )
 */
public function genererPostsAutomatiquement(Request $request)
{
    try {

        $posts = Posts::where('isValidated', true)->where('isPublished', false) ->where('datePost', '<=', now()) ->get();
        if ($posts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun post à traiter automatiquement.',
            ], 200);
        }
          foreach ($posts as $post) {

            
            $req = new Request([
                "post"        => $post->post,
                "titrePost"   => $post->titrePost,
                "url"         => $post->url,
                "id_post"     => $post->id,
                "now"         => true,                
                "datePost"    => $post->datePost,
                "network"     => $post->network,
                "idUser"      => $post->idUser,
                "isValidated" => 1,                    
            ]);

          
            $this->createPublishPost($req);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tous les posts validés ont été traités et publiés automatiquement.',
        ], 200);

        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur serveur : ' . $e->getMessage(),
            'status' => 500,
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
