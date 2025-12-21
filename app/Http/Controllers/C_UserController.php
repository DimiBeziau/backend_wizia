<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Abonnements;
use App\Models\Generation;
use App\Models\Limites;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PieceJointes;
use Illuminate\Support\Facades\Auth;

class C_UserController extends Controller
{

  /**
 * @OA\Get(
 *     path="/users/{id}",
 *     summary="Récupérer un utilisateur par ID",
 *     tags={"users"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'utilisateur",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="Utilisateur trouvé"),
 *     @OA\Response(response=404, description="Utilisateur non trouvé"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  // Récupérer un utilisateur par ID
  public function getUser($id)
  {
    try {
      $user = User::find($id);
      if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
      }
      return response()->json($user);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Erreur lors de la récupération de l\'utilisateur'], 500);
    }
  }
/**
 * @OA\Post(
 *     path="/users/sertchUser",
 *     summary="Rechercher un utilisateur via email et mot de passe",
 *     tags={"users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="userexample.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Utilisateur trouvé et authentifié"),
 *     @OA\Response(response=401, description="Email ou mot de passe invalide"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  public function sertchgetUser(Request $request)
  { // a voir 
    try {
      $request->validate([
        'email' => 'required|email',
        'password' => 'required',
      ]);

      $user = User::where('email', $request->email)->first();

      if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Email ou mot de passe invalide'], 401);
      }

      return response()->json($user, 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Erreur lors de la récupération de l\'utilisateur'], 500);
    }
  }
/**
 * @OA\Post(
 *     path="/auth/register",
 *     summary="Ajouter un utilisateur",
 *     tags={"auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","activity","password","password_confirmation","color","description"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="johnexample.com"),
 *             @OA\Property(property="activity", type="string", example="Coiffure"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
 *             @OA\Property(property="phone", type="string", example="0601020304"),
 *             @OA\Property(property="color", type="string", example="#FF5733"),
 *             @OA\Property(property="logo", type="string", example="https://example.com/logo.png"),
 *             @OA\Property(property="description", type="string", example="Description du profil"),
 *             @OA\Property(property="tone", type="string", example="Familier"),
 *             @OA\Property(property="goal", type="string", example="Vendre mes produits")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Utilisateur créé avec succès"),
 *     @OA\Response(response=400, description="Erreur de validation"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */


  // Ajouter un utilisateur notre client
  public function register(Request $request)
  {
  
    $request->validate([
  
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'activity' => 'required',
      'password' => 'required',
      'password_confirmation' => 'required|same:password',
      'phone' => 'nullable|numeric',
      'logo' => 'nullable|string',
      'color' => 'required',
      'description' => 'required',
      'companyName' => 'required',
      'tone' => 'required',
      'call' => 'required',
    ]);


    $user = new User();
    
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->phone = $request->phone;
    $user->activity = $request->activity;
    $user->logo = $request->logo;
    $user->color = $request->color;
    $user->description = $request->description;
    $user->companyName = $request->companyName;
    $user->tone = $request->tone;
    $user->call = $request->call;
    $user->idAbonnement = '1';
    $user->save();
    $token = $user->createToken('auth_token')->plainTextToken;

    $generation = new Generation();
    $generation->IdUser = $user->id;
    $generation->generation_Prompte = 0;
    $generation->generation_Picture = 0;
    $generation->generation_Newsletter = 0;
    $generation->nombre_Contact_Newsletter = 0;
    $generation->dateDebut = date('Y-m-01');
    $generation->dateFin = date('Y-m-t');
    $generation->save();
    return response()->json(['user' => $user, 'token' => $token], 200);
  }
 /**
 * @OA\Post(
 *     path="/auth/AuthenticatedUser",
 *     summary="Récupérer l'utilisateur actuellement authentifié",
 *     tags={"auth"},
 *     @OA\Response(response=200, description="Utilisateur authentifié retourné"),
 *     @OA\Response(response=401, description="Utilisateur non authentifié")
 * )
 */


  public function GetAuthenticatedUser(Request $request)
  {
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }
    return response()->json(["User" => $user], 200);
  }
/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Authentifier un utilisateur",
 *     tags={"auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="johnexample.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Authentification réussie, retourne token et user"),
 *     @OA\Response(response=401, description="Email ou mot de passe invalide"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  public function login(Request $request)
  {
    $email = $request->input('email');
    $password = $request->input('password');

    if (Auth::attempt(['email' => $email, 'password' => $password])) {
      $user = User::find(Auth::id());
      $token = $user->createToken('auth_token')->plainTextToken;
      return response()->json(['token' => $token, 'user' => $user], 200);
    } else {
      return response()->json(['message' => 'Email ou mot de passe invalide'], 401);
    }
  }
/**
 * @OA\Put(
 *     path="/users/{id}",
 *     summary="Mettre à jour un utilisateur",
 *     tags={"users"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'utilisateur",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="johnexample.com"),
 *             @OA\Property(property="number", type="string", example="0601020304"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="activity", type="string", example="coiffeur"),
 *             @OA\Property(property="description", type="string", example="description"),
 *             @OA\Property(property="companyName", type="string", example="WIZIA"),
 *             @OA\Property(property="tone", type="string", example="Familier"),
 *             @OA\Property(property="goal", type="string", example="Vendre mes produits"),
 *             @OA\Property(property="logo", type="string", example="https://example.com/logo.png"),
 *         )
 *     ),
 *     @OA\Response(response=200, description="Utilisateur mis à jour avec succès"),
 *     @OA\Response(response=404, description="Utilisateur non trouvé"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */


  // Mettre à jour un utilisateur
  public function updateUser(Request $request, $id)
  {
    try {
      $user = User::find($id);
      if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
      }

      $user->email = $request->email;
      $user->number = $request->number;
      $user->name = $request->name;
      $user->activity = $request->activity;
      $user->color = $request->color;
      $user->logo = $request->logo;
      $user->description = $request->description;
      $user->companyName = $request->companyName;
      $user->tone = $request->tone;
      $user->goal = $request->goal;
      $user->logo = $request->logo;
      //
      if ($user->password !== null && $request->has('password')) {
        $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->json(['message' => 'Utilisateur mis à jour avec succès']);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
    }
  }
/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     summary="Supprimer un utilisateur",
 *     tags={"users"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'utilisateur",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="Utilisateur supprimé avec succès"),
 *     @OA\Response(response=404, description="Utilisateur non trouvé"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  // Supprimer un utilisateur
  public function deleteUser($id)
  {
    try {
      $user = User::find($id);
      if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
      }

      $user->delete();
      return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Erreur lors de la suppression de l\'utilisateur'], 500);
    }
  }
  /**
 * @OA\Post(
 *     path="/users/uploadlogo",
 *     summary="Uploader l'image du logo de l'utilisateur",
 *     tags={"users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="file", type="file", description="Image à uploader (jpeg,png,jpg,gif)")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Image uploadée avec succès"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  public function uploadImage(Request $request)
  {
    try {
      $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

      ]);
      $image = $request->file('file');
      $imagePath = $image->store('logo', 'public');

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
 *     path="/users/abonnementUser",
 *     summary="Récupérer les informations d'abonnement et limites d'un utilisateur",
 *     tags={"users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Infos d'abonnement récupérées"),
 *     @OA\Response(response=404, description="Utilisateur ou abonnement non trouvé"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

  public static function abonnementUser( Request $request )
{
  $request->validate([
      'id' => 'required'
  ]);

  $id = $request->input('id');
    try {
        $user = User::find($id);
        if (!$user) {
            return [
                'error' => true,
                'message' => 'user non trouvé'
            ];
        }

        $abonnement = Abonnements::find($user->idAbonnement);
        if (!$abonnement) {
            return [
                'error' => true,
                'message' => 'Abonnement non trouvé'
            ];
        }

        $limit = limites::find($abonnement->id);
        $generation = Generation::find($id);

        // Déterminer le type d'abonnement
        if ($abonnement->isFree == 1) {
            $nomAbonement = "isFree";
        } elseif ($abonnement->isPremium == 1) {
            $nomAbonement = "isPremium";
        } elseif ($abonnement->isProfessionnel == 1) {
            $nomAbonement = "isProfessionnel";
        } else {
            $nomAbonement = "unknown";
        }
        

        return [
            'error' => false,
            'AbonementType' => $nomAbonement,
            'PictureLimite' => $limit->isLimiteImage ?? 0,
            'TexteLimite' => $limit->isLimitTexte ?? 0,
            'UsedPicture' => $generation->generation_Picture ?? 0,
            'UsedTexte' => $generation->generation_Prompte ?? 0,
        ];
    } catch (\Exception $e) {
        return [
            'error' => true,
            'message' => 'Erreur lors de la récupération de l\'abonnement'
        ];
    }
}
}

