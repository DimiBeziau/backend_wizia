<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\User;
use App\Models\Abonnements;


class C_StripeController extends Controller
{
    /**
 * @OA\Post(
 *     path="/stripe/create-payment-intent",
 *     summary="Créer un PaymentIntent Stripe et mettre à jour l'abonnement utilisateur",
 *     tags={"Payment"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"amount","IdUser","email","nom"},
 *             @OA\Property(property="amount", type="number", format="float", example=2000, description="Montant en centimes"),
 *             @OA\Property(property="IdUser", type="integer", example=1, description="ID de l'utilisateur"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Email pour le reçu"),
 *             @OA\Property(property="nom", type="string", example="Premium", description="Nom de l'abonnement (Free, Premium, Professionnel)")
 *         )
 *     ),
 *     @OA\Response(response=200, description="PaymentIntent créé avec succès et abonnement mis à jour"),
 *     @OA\Response(response=400, description="Erreur de validation ou abonnement non valide"),
 *     @OA\Response(response=404, description="Utilisateur ou abonnement introuvable"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'IdUser' => 'required|numeric',
            'email' => 'required|email',
            'nom' => 'required|string|max:255',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $intent = PaymentIntent::create([
                'amount' => $validated['amount'],
                'currency' => 'eur',
                'receipt_email' => $validated['email'],
                'metadata' => [
                    'integration_check' => 'accept_a_payment',
                ],
            ]);
            // Récupérer l'utilisateur
        $user = User::find($validated['IdUser']);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable.'], 404);
        }
        $abonnement = Abonnements::find($user->idAbonnement);
        if (!$abonnement) {
            return response()->json(['error' => 'Abonnement introuvable.'], 404);
        }
        // Créer un abonnement avec les bons flags
        $abonnement->isFree = 0;
        $abonnement->isPremium = 0;
        $abonnement->isProfessionnel = 0;

        switch ($validated['nom']) {
            case 'Free':
                $abonnement->isFree = true;
                break;
            case 'Premium':
                $abonnement->isPremium = true;
                break;
            case 'Professionnel':
                $abonnement->isProfessionnel = true;
                break;
            default:
                return response()->json(['error' => 'Abonnement non valide'], 400);
        }

        $abonnement->save();

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
 * @OA\Get(
 *     path="/stripe/abonnement/{id}",
 *     summary="Récupérer le type d'abonnement d'un utilisateur",
 *     tags={"Payment"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'utilisateur",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="Type d'abonnement retourné (isFree, isPremium, isProfessionnel)"),
 *     @OA\Response(response=400, description="Aucun type d’abonnement valide"),
 *     @OA\Response(response=404, description="Utilisateur ou abonnement non trouvé"),
 *     @OA\Response(response=500, description="Erreur serveur")
 * )
 */

    public function getAbonnement($id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $idAbonnement = $user->idAbonnement; 
        
        $abonnement = Abonnements::find($idAbonnement);

        if (!$abonnement) {
            return response()->json(['message' => 'Abonnement non trouvé'], 404);
        }

        if ($abonnement->isFree == 1) {
            return response()->json("isFree", 200);
        } elseif ($abonnement->isPremium == 1) {
            return response()->json("isPremium", 200);
        } elseif ($abonnement->isProfessionnel == 1) {
            return response()->json("isProfessionnel", 200);
        }

        return response()->json(['message' => 'Aucun type d’abonnement valide'], 400);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la récupération de l\'abonnement', 'error' => $e->getMessage()], 500);
    }
}


}
