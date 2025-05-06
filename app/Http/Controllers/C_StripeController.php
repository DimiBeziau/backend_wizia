<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

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
}
