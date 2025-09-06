<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Posts;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class C_NetwoorkController extends Controller
{
  public function createAndPublishPostFacebook(Request $request)
  {
    $postData = $request->input('post');

    $userId = Auth::id() ?? 1;

    if ($request->input('date') === null && $request->input('now') === true) {
      $date = new DateTime();
      $date = $date->format('Y-m-d');
    } else {
      $date = $request->input('date');
    }

    Posts::create([
      "datePost" => $date,
      "idUser" => $userId,
      "idPieceJointe" => 0,
      "post" => $postData
    ]);


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
  public function createAndPublishPostInstagrame(Request $request){
    $postData = $request->input('post');

    $userId = Auth::id() ?? 1;

    if ($request->input('date') === null && $request->input('now') === true) {
      $date = new DateTime();
      $date = $date->format('Y-m-d');
    } else {
      $date = $request->input('date');
    }

    Posts::create([
      "datePost" => $date,
      "idUser" => $userId,
      "idPieceJointe" => 0,
      "post" => $postData
    ]);

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
  }
  public function createAndPublishPostLinkeding(Request $request)
{
    $postData = $request->input('post');
    $userId = Auth::id() ?? 1;

    // Gestion de la date
    if ($request->input('date') === null && $request->input('now') === true) {
        $date = now()->format('Y-m-d');
    } else {
        $date = $request->input('date');
    }

    // Enregistrer le post
    Posts::create([
        "datePost" => $date,
        "idUser" => $userId,
        "idPieceJointe" => 0,
        "post" => $postData
    ]);

    // Si on doit publier maintenant
    if ($request->input('now') === true) {
        $data = [
            "Post" => $postData
        ];

        $url = 'https://hook.eu2.make.com/8wla3eo601n8ii146jwuoezmja07vs3r';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json', 
            'Accept' => 'application/json',
            'x-make-apikey' => env("KeyLinkeding")
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

}
