<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Abonnements;
use Illuminate\Http\Request;
use App\Models\User;
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
    try {
      $request->validate([
        'firstName' => 'required',
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
        'pasword_confirmation' => 'required|same:password',
        'Number' => 'nullable|numeric'
      ]);
      
      $user = new User();
      $user->firstName = $request->firstName;
      $user->name = $request->name;
      $user->email = $request->email;
      $user->password = password_hash($request->password, PASSWORD_DEFAULT);
      $user->number = $request->Number;
      $user->idAbonnement ='1'; 
      $user->save();

      return response()->json($user, 200);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Erreur lors de l\'ajout de l\'utilisateur',
        'error' => $e->getMessage()
      ], 500);
    }
    
  }
  public function GetAuthenticatedUser(Request $request)
  {
    $user = Auth::user();
    if (!$user) {
      return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }
    return response()->json(["User"=>$user], 200);
  }

  public function login(Request $request)
  {
    $email = $request->input('email');
    $password = $request->input('password');

    Auth::attempt(['email' => $email, 'password' => $password]);
    $user = User::where('email', $email)->first();
    if ($user) {
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

      $user->name = $request->name;
      $user->email = $request->email;
      $user->number = $request->number;
      $user->firstName = $request->firstName;
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
  // public function index(){
  //             $users = User::all();
  //             dd($users);
  //         }

}

// namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;

// class UserController extends Controller
// {
//     public function index(){
//         // $users = User::all();
//         // dd($users);
//     }
// }
