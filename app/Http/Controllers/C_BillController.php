<?php

namespace App\Http\Controllers;

require('../../../vendor/autoload.php');
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FacturePDF;

class C_BillController extends Controller
{
    public function generateBill(Request $request)
    {
        $client = [
            'nom' => $request->input('nom'),
            'adresse' => $request->input('adresse'),
        ];

        $articles = $request->input('articles');

        $facture = new FacturePDF();
        $facture->createBill($client, $articles);

        return response()->json(['message' => 'Facture générée avec succès']);
    }
}
