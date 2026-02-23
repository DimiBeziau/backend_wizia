<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;

class C_MailSettingsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/mail/ListDestinataireClient/{idUser}",
     *     summary="Récupère la liste des destinataires d'un utilisateur",
     *     tags={"Destinataires"},
     *
     *     @OA\Parameter(
     *         name="idUser",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des destinataires",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="mail", type="string"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="prenom", type="string")
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="ID invalide"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function getListDestinataire($idUser)
    {
        try {
            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID invalide',
                ], 400);
            }

            $clients = Clients::where('idUser', $idUser)->get();

            return response()->json([
                'success' => true,
                'data' => [$clients],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des destinataires',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Récupération des destinataires par email et idUser
    public function getListDestinataireEmail(Request $request)
    {
        try {
            $emails = $request->input('mail', []);
            $idUser = $request->input('idUser');

            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID invalide',
                ], 400);
            }

            $clients = Clients::where('idUser', $idUser)
                ->whereIn('mail', $emails)
                ->get(['id', 'mail']);

            return response()->json([
                'success' => true,
                'data' => $clients->pluck('id')->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des destinataires',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/mail/AddDestinataireClient/{idUser}",
     *     summary="Ajoute un destinataire",
     *     tags={"Destinataires"},
     *
     *     @OA\Parameter(
     *         name="idUser",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"mail", "nom", "prenom"},
     *
     *             @OA\Property(property="mail", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="nom", type="string", example="Doe"),
     *             @OA\Property(property="prenom", type="string", example="John")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Destinataire ajouté",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="ID invalide"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function addListDestinataire(Request $request, $idUser)
    {
        try {
            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID invalide',
                ], 400);
            }

            $request->validate([
                'mail' => 'required|email',
                'nom' => 'required|string',
                'prenom' => 'required|string',
            ]);

            $client = new Clients;
            $client->idUser = $idUser;
            $client->mail = $request->mail;
            $client->nom = $request->nom;
            $client->prenom = $request->prenom;
            $client->save();

            return response()->json([
                'success' => true,
                'message' => 'Destinataire ajouté avec succès',
                'data' => $client,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du destinataire',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/mail/UpdateDestinataireClient/{idUser}",
     *     summary="Met à jour un destinataire",
     *     tags={"Destinataires"},
     *
     *     @OA\Parameter(
     *         name="idUser",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"id"},
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="mail", type="string", format="email"),
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Destinataire mis à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Destinataire non trouvé"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function updateListDestinataire(Request $request, $idUser)
    {
        try {
            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID utilisateur invalide',
                ], 400);
            }

            $request->validate([
                'id' => 'required|integer',
                'mail' => 'sometimes|email',
                'nom' => 'sometimes|string',
                'prenom' => 'sometimes|string',
            ]);

            $client = Clients::where('id', $request->id)->where('idUser', $idUser)->first();

            if (! $client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinataire non trouvé pour cet utilisateur',
                ], 404);
            }

            $client->mail = $request->input('mail', $client->mail);
            $client->nom = $request->input('nom', $client->nom);
            $client->prenom = $request->input('prenom', $client->prenom);
            $client->save();

            return response()->json([
                'success' => true,
                'message' => 'Destinataire mis à jour avec succès',
                'data' => $client,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ListDestinatairebyMail($mailDestinataire)
    {
        try {
            $client = Clients::where('mail', $mailDestinataire)->first();

            if (! $client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinataire non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $client,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/mail/DeleteListDestinataire/{idDestinataire}",
     *     summary="Supprime un destinataire",
     *     tags={"Destinataires"},
     *
     *     @OA\Parameter(
     *         name="idDestinataire",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Destinataire supprimé",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Destinataire non trouvé"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function deleteListDestinataire($idDestinataire)
    {
        try {
            $client = Clients::find($idDestinataire);

            if (! $client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinataire non trouvé',
                ], 404);
            }

            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Destinataire supprimé avec succès',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
