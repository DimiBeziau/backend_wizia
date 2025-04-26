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
  public function createAndPublishPost(Request $request)
  {
    $post = $request->input('post');

    $today = new DateTime();
    $today = $today->format('Y-m-d');

    $userId = Auth::id() ?? 1;



    Posts::create([
      "datePost" => $today,
      "idUser" => $userId,
      "idPieceJointe" => 0,
      "post" => $post
    ]);

    $url = 'https://hook.eu2.make.com/umhsf8kaax437qklfxrf7oechd4hp3qk';
    $data = [
      'post' => $post
    ];

    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ])->post($url, $data);

    return response()->json([
      'status' => $response->status(),
      'body' => $response->body(),
      'json' => $response->json(),
    ]);
  }
}
