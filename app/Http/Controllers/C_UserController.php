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

  // Ajouter un utilisateur notre client
  public function register(Request $request)
  {
    // try {
    $request->validate([
      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|email|unique:users',
      'activity' => 'required',
      'password' => 'required',
      'password_confirmation' => 'required|same:password',
      'phone' => 'nullable|numeric',
      'logo' => 'nullable|string',
      'color' => 'required',
      'description' => 'required',
    ]);


    $user = new User();
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->phone = $request->phone;
    $user->activity = $request->activity;
    $user->logo = $request->logo;
    $user->color = $request->color;
    $user->description = $request->description;
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
    // } catch (\Exception $e) {
    //   return response()->json([
    //     'message' => 'Erreur lors de l\'ajout de l\'utilisateur',
    //     'error' => $e->getMessage()
    //   ], 500);
    // }

  }
  public function GetAuthenticatedUser(Request $request)
  {
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }
    return response()->json(["User" => $user], 200);
  }

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

  // Mettre à jour un utilisateur
  public function updateUser(Request $request, $id)
  {
    try {
      $user = User::find($id);
      if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
      }

      $user->last_name = $request->last_name;
      $user->email = $request->email;
      $user->number = $request->number;
      $user->first_name = $request->first_name;
      if ($user->password !== null && $request->has('password')) {
        $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->json(['message' => 'Utilisateur mis à jour avec succès']);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
    }
  }

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
  public function uploadImage(Request $request)
  {
    try {
      $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

      ]);
      $image = $request->file('file');
      $imagePath = $image->store('logo', 'public');

      // $pieceJointe = new PieceJointes();
      // $pieceJointe->path = $imagePath;
      // $pieceJointe->type = $request->type; 
      // $pieceJointe->idUser = $request->id; 
      // $pieceJointe->save();

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

