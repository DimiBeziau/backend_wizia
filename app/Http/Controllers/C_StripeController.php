<?php

namespace App\Http\Controllers;

use App\Models\Abonnements;
use App\Models\PaymentUser;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class C_StripeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/stripe/create-payment-intent",
     *     summary="Créer un PaymentIntent Stripe et mettre à jour l'abonnement utilisateur",
     *     tags={"Payment"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"amount","IdUser","email","nom"},
     *
     *             @OA\Property(property="amount", type="number", format="float", example=2000, description="Montant en centimes"),
     *             @OA\Property(property="IdUser", type="integer", example=1, description="ID de l'utilisateur"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Email pour le reçu"),
     *             @OA\Property(property="nom", type="string", example="Premium", description="Nom de l'abonnement (Free, Premium, Professionnel)")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="PaymentIntent créé avec succès et abonnement mis à jour"),
     *     @OA\Response(response=400, description="Erreur de validation ou abonnement non valide"),
     *     @OA\Response(response=404, description="Utilisateur ou abonnement introuvable"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'IdUser' => 'required|numeric',
            'nom' => 'required|string|max:255',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $UserId = $validated['IdUser'];
            // Récupérer l'utilisateur
            $user = User::find($UserId);
            if (! $user) {
                return response()->json(['error' => 'Utilisateur introuvable.'], 404);
            }
            $id = 0;
            switch ($validated['nom']) {
                case 'Free':
                    $id = 1;
                    if ($user->idAbonnement != $id) {
                        $user->idAbonnement = $id;
                        $user->save();
                    }
                    break;
                case 'Premium':
                    $id = 2;
                    if ($user->idAbonnement != $id) {
                        $user->idAbonnement = $id;
                        $user->save();
                    }
                    break;
                case 'Professionnel':
                    $id = 3;
                    if ($user->idAbonnement != $id) {
                        $user->idAbonnement = $id;
                        $user->save();
                    }
                    break;
                default:
                    return response()->json(['error' => 'Abonnement non valide'], 400);
            }
            $typePayement = 'stripe';
            $typemonnaie = 'eur';

            $abonnement = Abonnements::find($user->idAbonnement);
            if (! $abonnement) {
                return response()->json(['error' => 'Abonnement introuvable.'], 404);
            }

            // Créer le PaymentIntent
            $intent = PaymentIntent::create([
                'amount' => $abonnement->prix * 100, // * 100,
                'currency' => $typemonnaie,
                'receipt_email' => $user->email,
                'metadata' => [
                    'integration_check' => 'accept_a_payment',
                ],
            ]);

            $paymentUser = new PaymentUser;
            $paymentUser->idUser = $UserId;
            $paymentUser->idAbonnements = $user->idAbonnement;
            $paymentUser->datePayement = date('Y-m-d H:i:s');
            $paymentUser->dateStart = now();
            $paymentUser->dateEnd = now()->addMonth();
            $paymentUser->dateCancel = null;
            $paymentUser->cancelAbonnement = false;
            $paymentUser->paymentMethod = $typePayement;
            $paymentUser->idTransaction = $intent->id;
            $paymentUser->currency = $typemonnaie;
            $paymentUser->isRecurring = true;
            $paymentUser->notes = '';
            $paymentUser->save();

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
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
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
            if (! $user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            $idAbonnement = $user->idAbonnement;

            $abonnement = Abonnements::find($idAbonnement);

            if (! $abonnement) {
                return response()->json(['message' => 'Abonnement non trouvé'], 404);
            }

            if ($abonnement->isFree == 1) {
                return response()->json('isFree', 200);
            } elseif ($abonnement->isPremium == 1) {
                return response()->json('isPremium', 200);
            } elseif ($abonnement->isProfessionnel == 1) {
                return response()->json('isProfessionnel', 200);
            }

            return response()->json(['message' => 'Aucun type d’abonnement valide'], 400);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération de l\'abonnement', 'error' => $e->getMessage()], 500);
        }
    }
}
