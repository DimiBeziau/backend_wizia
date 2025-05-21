<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\User;
use App\Models\Abonnements;

class C_StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'email' => 'required|email',
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

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
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
