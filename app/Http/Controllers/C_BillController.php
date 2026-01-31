<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class C_BillController extends Controller
{
    /**
     * @OA\Post(
     *     path="/bill/generatebill",
     *     summary="Génère une facture",
     *     tags={"Factures"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="nom", type="string", example="Jean Dupont"),
     *             @OA\Property(property="adresse", type="string", example="12 Rue de Paris"),
     *             @OA\Property(
     *                 property="articles",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="designation", type="string", example="Bière artisanale"),
     *                     @OA\Property(property="prix", type="number", format="float", example=5.90),
     *                     @OA\Property(property="quantite", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Facture générée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Facture générée avec succès")
     *         )
     *     )
     * )
     */
    public function generateBill(Request $request)
    {
        $client = [
            'nom' => $request->input('nom'),
            'adresse' => $request->input('adresse'),
        ];

        $articles = $request->input('articles');

        $facture = new C_BillCreate;
        $facture->createBill($client, $articles);

        return response()->json(['message' => 'Facture générée avec succès']);
    }
}
