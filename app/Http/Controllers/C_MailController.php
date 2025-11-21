<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Models\Clients;
use App\Models\Mailings;
use App\Models\ClientsMailings;
use App\Models\PieceJointes;
use App\Models\PieceJointeMailings;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

///use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;


class C_MailController extends Controller
{
 
  private $mail;

  public function __construct($debug = false)
  {
    $this->mail = new PHPMailer($debug);
    if ($debug) {
      $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
    }
    $this->configureSMTP();
  }

  private function configureSMTP()
  {
    $dotenv = Dotenv::createImmutable(base_path());
    $dotenv->load();

    $this->mail->isSMTP();
    $this->mail->SMTPAuth = true;
    $this->mail->Host = env('MAIL_HOST');
    $this->mail->Port = env('MAIL_PORT');
    $this->mail->Username = env('MAIL_USERNAME');
    $this->mail->Password = env('MAIL_PASSWORD');
    $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  }
 public function addAttachment($mail, $filePath, $fileName)
{
    if (file_exists($filePath)) {
        $mail->addAttachment($filePath, $fileName);
    }
}

  /**
   * @OA\Post(
   *     path="/mail/generateMail",
   *     summary="Envoie un email avec PHPMailer",
   *     tags={"Mailing"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 type="object",
   *                 required={"to", "subject", "body"},
   *                 @OA\Property(property="to", type="array", @OA\Items(type="string", format="email"), example={"john@example.com", "jane@example.com"}),
   *                 @OA\Property(property="subject", type="string", example="Votre commande"),
   *                 @OA\Property(property="body", type="string", example="<h1>Bonjour</h1><p>Merci pour votre commande</p>"),
   *                 @OA\Property(property="altBody", type="string", example="Version texte de l'email"),
   *                 @OA\Property(property="fromName", type="string", example="WIZIA"),
   *                 @OA\Property(property="fromEmail", type="string", format="email", example="contact@wizia.com"),
   *                 @OA\Property(property="file", type="array", @OA\Items(type="string", format="binary")),
   *                 @OA\Property(property="idMailing", type="integer", example=1)
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Email envoyé avec succès",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="Email(s) envoyé(s) avec succès"),
   *             @OA\Property(property="success", type="boolean", example=true)
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Erreur lors de l'envoi",
   *         @OA\JsonContent(
   *             @OA\Property(property="error", type="string"),
   *             @OA\Property(property="success", type="boolean", example=false)
   *         )
   *     )
   * )
   */
  public function generateMail(Request $request)
  {

    $request->validate([
      'to' => 'required|array',
      'to.*' => 'email',
      'subject' => 'required|string',
      'body' => 'required|string',
      'altBody' => 'nullable|string',
      'fromName' => 'nullable|string',
      'fromEmail' => 'nullable|email',
      'file' => 'nullable|array',
      'file.*' => 'file|max:10240',
      'idMailing' => 'nullable|integer',
    ]);
    try {
      $to = $request->input('to');
      $subject = $request->input('subject');
      $body = $request->input('body');
      $altBody = $request->input('altBody', '');
      $fromName = $request->input('fromName', 'WIZIA');
      $fromEmail = $request->input('fromEmail', 'contact@dimitribeziau.fr');
      $file = $request->file('file');
      $idMail = $request->file('idMailing');

      foreach ($to as $destinataire) {

        $mail = clone $this->mail;
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($destinataire);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        // $mail->addCC('cc1@exemple.com', 'Elena'); // CC et BCC
        // $mail->addBCC('bcc1@exemple.com', 'Alex');// CC et BCC
        if ($file) {
    if (is_array($file)) {
        foreach ($file as $file) {
            $this->addAttachment($mail, $file->getRealPath(), $file->getClientOriginalName());
        }
    } else {
        $this->addAttachment($mail, $file->getRealPath(), $file->getClientOriginalName());
    }
}
        if (!$mail->send()) {
          throw new \Exception("Échec de l'envoi à $destinataire : " . $mail->ErrorInfo);
        }
      }
      if($idMail!= null){
       $request = new \Illuminate\Http\Request();
        $request->merge(['id_mail' => $idMail]);
        $this->publishedMail($request); 
      }  
      return response()->json(['message' => 'Email(s) envoyé(s) avec succès', 'success' => true], 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage(), 'success' => false], 500);
    }
  }
   /**
   * @OA\Post(
   *     path="/mail/AddMail/{idUser}",
   *     summary="Ajoute un mailing en base de données",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idUser",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer"),
   *         description="ID de l'utilisateur"
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(
   *                 type="object",
   *                 required={"to", "toListId", "subject", "body"},
   *                 @OA\Property(property="to", type="array", @OA\Items(type="string", format="email")),
   *                 @OA\Property(property="toListId", type="array", @OA\Items(type="integer")),
   *                 @OA\Property(property="subject", type="string", example="Newsletter Janvier"),
   *                 @OA\Property(property="body", type="string", example="<p>Contenu du mail</p>"),
   *                 @OA\Property(property="altBody", type="string"),
   *                 @OA\Property(property="fromName", type="string"),
   *                 @OA\Property(property="fromEmail", type="string", format="email"),
   *                 @OA\Property(property="file", type="array", @OA\Items(type="string", format="binary")),
   *                 @OA\Property(property="date", type="string", format="date-time"),
   *                 @OA\Property(property="isValidated", type="boolean"),
   *                 @OA\Property(property="isPublished", type="boolean")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Mail ajouté avec succès",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string")
   *         )
   *     ),
   *     @OA\Response(response=400, description="ID invalide"),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */
  public function AddMail(Request $request, $idUser) 
  {
    try {
      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID invalide'
        ], 400);
      }

      $validated = $request->validate([
        'to' => 'required|array',
        'to.*' => 'email',
        'toListId' => 'required|array',
        'toListId.*' => 'integer',
        'subject' => 'required|string',
        'body' => 'required|string',
        'altBody' => 'nullable|string',
        'fromName' => 'nullable|string',
        'fromEmail' => 'nullable|email',
        'file' => 'nullable|array',
        'file.*' => 'file|max:10240',
        'date' => 'nullable|dateteime',
        'isValidated' => 'boolean',
        'isPublished' => 'boolean',
      ]);

      $mail = new Mailings();
      $mail->idUser  = $idUser;
      $mail->subject = $validated['subject'];
      $mail->body = $validated['body'];
      $mail->altBody = $validated['altBody'] ?? null;
      $mail->fromName = $validated['fromName'] ?? null;
      $mail->fromEmail = $validated['fromEmail'] ?? null;
      $mail->fromEmail = $validated['isPublished'] ?? false;
      $mail->fromEmail = $validated['isValidated'] ?? false;
      $mail->date = $validated['date'] ?? date('Y-m-d H:i:s'); 
      $mail->save();
      
      foreach ($validated['toListId'] as $destId) {
        $ClientsMailings = new ClientsMailings();
        $ClientsMailings->idMailing = $mail->id;
        $ClientsMailings->idClient = $destId;
        $ClientsMailings->save();
      }


       foreach ($validated['file'] as $file) {
      $pieceJointes = new PieceJointes();
      $pieceJointes->type = $file ? $file->getRealPath() : null;
      $pieceJointes->idUser = $idUser;
      $pieceJointes->path = null;

      $pieceJointes = new PieceJointeMailings();
      $pieceJointes-> idPieceJointe = $pieceJointes->id;
      $pieceJointes-> idMailing = $mail->id;
      $pieceJointes->save();  

      $pieceJointes->save();
       }
       
    
      return response()->json([
        'success' => true,
        'message' => 'Mail ajouté avec succès',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de l\'ajout du mail',
        'error' => $e->getMessage()
      ], 500);
    }
  }
 /**
   * @OA\Get(
   *     path="/mail/ListDestinataireClient/{idUser}",
   *     summary="Récupère la liste des destinataires d'un utilisateur",
   *     tags={"Destinataires"},
   *     @OA\Parameter(
   *         name="idUser",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Liste des destinataires",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="data", type="array", @OA\Items(
   *                 @OA\Property(property="id", type="integer"),
   *                 @OA\Property(property="mail", type="string"),
   *                 @OA\Property(property="nom", type="string"),
   *                 @OA\Property(property="prenom", type="string")
   *             ))
   *         )
   *     ),
   *     @OA\Response(response=400, description="ID invalide"),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */
  public function getListDestinataire($idUser)
  {
    try {

      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID invalide'
        ], 400);
      }

      $clients = clients::where('idUser', $idUser)->get();

      return response()->json([
        'success' => true,
        'data' => $clients
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération des destinataires',
        'error' => $e->getMessage()
      ], 500);
    }
  }
 /**
   * @OA\Post(
   *     path="/mail/AddDestinataireClient/{idUser}",
   *     summary="Ajoute un destinataire",
   *     tags={"Destinataires"},
   *     @OA\Parameter(
   *         name="idUser",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"mail", "nom", "prenom"},
   *             @OA\Property(property="mail", type="string", format="email", example="john.doe@example.com"),
   *             @OA\Property(property="nom", type="string", example="Doe"),
   *             @OA\Property(property="prenom", type="string", example="John")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Destinataire ajouté",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string"),
   *             @OA\Property(property="data", type="object")
   *         )
   *     ),
   *     @OA\Response(response=400, description="ID invalide"),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */

  public function addListDestinataire(Request $request, $idUser)
  {
    try {
      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID invalide'
        ], 400);
      }

      $request->validate([
        'mail' => 'required|email',
        'nom' => 'required|string',
        'prenom' => 'required|string',
      ]);

      $client = new clients();
      $client->idUser = $idUser;
      $client->mail = $request->mail;
      $client->nom = $request->nom;
      $client->prenom = $request->prenom;
      $client->save();

      return response()->json([
        'success' => true,
        'message' => 'Destinataire ajouté avec succès',
        'data' => $client
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de l\'ajout du destinataire',
        'error' => $e->getMessage()
      ], 500);
    }
  }
 /**
   * @OA\Put(
   *     path="/mail/UpdateDestinataireClient/{idUser}",
   *     summary="Met à jour un destinataire",
   *     tags={"Destinataires"},
   *     @OA\Parameter(
   *         name="idUser",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"id"},
   *             @OA\Property(property="id", type="integer", example=1),
   *             @OA\Property(property="mail", type="string", format="email"),
   *             @OA\Property(property="nom", type="string"),
   *             @OA\Property(property="prenom", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Destinataire mis à jour",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string"),
   *             @OA\Property(property="data", type="object")
   *         )
   *     ),
   *     @OA\Response(response=404, description="Destinataire non trouvé"),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */
  public function updateListDestinataire(Request $request, $idUser)
  {
    try {
      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID utilisateur invalide'
        ], 400);
      }

      $request->validate([
        'id' => 'required|integer',
        'mail' => 'sometimes|email',
        'nom' => 'sometimes|string',
        'prenom' => 'sometimes|string',
      ]);

      $client = clients::where('id', $request->id)->where('idUser', $idUser)->first();

      if (!$client) {
        return response()->json([
          'success' => false,
          'message' => 'Destinataire non trouvé pour cet utilisateur'
        ], 404);
      }

      $client->mail = $request->input('mail', $client->mail);
      $client->nom = $request->input('nom', $client->nom);
      $client->prenom = $request->input('prenom', $client->prenom);
      $client->save();

      return response()->json([
        'success' => true,
        'message' => 'Destinataire mis à jour avec succès',
        'data' => $client
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour',
        'error' => $e->getMessage()
      ], 500);
    }
  }
 /**
   * @OA\Delete(
   *     path="/mail/DeleteListDestinataire/{idDestinataire}",
   *     summary="Supprime un destinataire",
   *     tags={"Destinataires"},
   *     @OA\Parameter(
   *         name="idDestinataire",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Destinataire supprimé",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string")
   *         )
   *     ),
   *     @OA\Response(response=404, description="Destinataire non trouvé"),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */
  public function deleteListDestinataire($idDestinataire)
  {
    try {
      $client = clients::find($idDestinataire);

      if (!$client) {
        return response()->json([
          'success' => false,
          'message' => 'Destinataire non trouvé'
        ], 404);
      }

      $client->delete();

      return response()->json([
        'success' => true,
        'message' => 'Destinataire supprimé avec succès'
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la suppression',
        'error' => $e->getMessage()
      ], 500);
    }
  }
  /**
   * @OA\Get(
   *     path="/mail/ListMailingUser/{idUser}",
   *     summary="Récupère tous les mailings d'un utilisateur",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idUser",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Liste des mailings",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
   *         )
   *     )
   * )
   */
  public function getListMailingUser($idUser)
  {
    try {
      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID utilisateur invalide'
        ], 400);
      }

      $mailings = Mailings::where('idUser', $idUser)->get();

      return response()->json([
        'success' => true,
        'data' => $mailings
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération des mailings',
        'error' => $e->getMessage()
      ], 500);
    }
  }
/**
   * @OA\Get(
   *     path="/mail/ListMailingsendClient/{idMail}",
   *     summary="Récupère un mailing avec ses destinataires",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idMail",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Mailing avec destinataires",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="mailing", type="object"),
   *                 @OA\Property(property="clients", type="array", @OA\Items(type="object"))
   *             )
   *         )
   *     ),
   *     @OA\Response(response=404, description="Mailing non trouvé")
   * )
   */
  public function getListMailingWhithSendClients($idMail) 
  {
      try {
          // Vérification que l'ID est bien numérique
          if (!ctype_digit((string)$idMail)) {
              return response()->json([
                  'success' => false,
                  'message' => 'ID invalide'
              ], 400);
          }
  
          // Récupération du mailing
          $mailing = Mailings::find($idMail);
          if (!$mailing) {
              return response()->json([
                  'success' => false,
                  'message' => 'Mailing non trouvé'
              ], 404);
          }
  
          // Récupération optimisée des clients liés
          $clients = Clients::whereIn('id', function($query) use ($idMail) {
              $query->select('idClient')
                    ->from('clients_mailings')
                    ->where('idMailing', $idMail);
          })->get(['id', 'mail', 'nom', 'prenom']);
  
          return response()->json([
              'success' => true,
              'data' => [
                  'mailing' => $mailing,
                  'clients' => $clients
              ]
          ], 200);
  
      } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'message' => 'Erreur lors de la récupération du mailing',
              'error' => $e->getMessage()
          ], 500);
      }
  }
  /**
   * @OA\Get(
   *     path="/mail/SearchMailing/{idMailing}",
   *     summary="Récupère un mailing par son ID",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idMailing",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Détails du mailing",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="data", type="object")
   *         )
   *     ),
   *     @OA\Response(response=404, description="Mailing non trouvé")
   * )
   */
 public function SearchMailingById($idMailing)
{
    try {
        if (!ctype_digit((string)$idMailing)) {
            return response()->json([
                'success' => false,
                'message' => 'ID invalide'
            ], 400);
        }

        $mailing = Mailings::find($idMailing);
        if (!$mailing) {
            return response()->json([
                'success' => false,
                'message' => 'Mailing non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mailing
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération du mailing',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
   * @OA\Put(
   *     path="/mail/UpdateMailing/{idMailing}",
   *     summary="Met à jour un mailing",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idMailing",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             required={"subject", "body"},
   *             @OA\Property(property="subject", type="string"),
   *             @OA\Property(property="body", type="string"),
   *             @OA\Property(property="altBody", type="string"),
   *             @OA\Property(property="fromName", type="string"),
   *             @OA\Property(property="fromEmail", type="string", format="email"),
   *             @OA\Property(property="isValidated", type="boolean"),
   *             @OA\Property(property="isPublished", type="boolean")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Mailing mis à jour",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string"),
   *             @OA\Property(property="data", type="object")
   *         )
   *     ),
   *     @OA\Response(response=404, description="Mailing non trouvé")
   * )
   */
// Mettre à jour un mailing
public function updateMailing(Request $request, $idMailing)
{

    try {
        // Vérification que l'ID est bien un entier positif
        if (!ctype_digit((string)$idMailing)) {
            return response()->json([
                'success' => false,
                'message' => 'ID invalide'
            ], 400);
        }

        // Récupération du mailing
        $mailing = Mailings::find($idMailing);
        if (!$mailing) {
            return response()->json([
                'success' => false,
                'message' => 'Mailing non trouvé'
            ], 404);
        }

        // Validation des champs reçus
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'altBody' => 'nullable|string',
            'fromName' => 'nullable|string',
            'fromEmail' => 'nullable|email',
            'isValidated' => 'boolean',
            'isPublished' => 'boolean',
        ]);

        // Mise à jour des données
        $mailing->subject = $validated['subject'];
        $mailing->body = $validated['body'];
        $mailing->altBody = $validated['altBody'] ?? $mailing->altBody;
        $mailing->fromName = $validated['fromName'] ?? $mailing->fromName;
        $mailing->fromEmail = $validated['fromEmail'] ?? $mailing->fromEmail;
        $mailing->fromEmail = $validated['isValidated'] ?? $mailing->isValidated;
        $mailing->fromEmail = $validated['isPublished'] ?? $mailing->isPublished;
        $mailing->date = date('Y-m-d H:i:s'); 
        $mailing->save();

        return response()->json([
            'success' => true,
            'message' => 'Mailing mis à jour avec succès',
            'data' => $mailing
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du mailing',
            'error' => $e->getMessage()
        ], 500);
    }
}

 /**
   * @OA\Delete(
   *     path="/mail/DeleteMailing/{idMailing}",
   *     summary="Supprime un mailing",
   *     tags={"Mailing"},
   *     @OA\Parameter(
   *         name="idMailing",
   *         in="path",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Mailing supprimé",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Mailing supprimé avec succès")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Mailing non trouvé",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string")
   *         )
   *     ),
   *     @OA\Response(response=500, description="Erreur serveur")
   * )
   */
// Supprimer un mailing
public function deleteMailing($idMailing)
{
    try {
        if (!ctype_digit((string)$idMailing)) {
            return response()->json([
                'success' => false,
                'message' => 'ID invalide'
            ], 400);
        }

        $mailing = Mailings::find($idMailing);
        if (!$mailing) {
            return response()->json([
                'success' => false,
                'message' => 'Mailing non trouvé'
            ], 404);
        }

        $mailing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mailing supprimé avec succès'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression du mailing',
            'error' => $e->getMessage()
        ], 500);
    }
}

  public function validatedMail(Request $request){
   $validated =  $request->validate([
        'id_mail' => 'required|integer',
    ]);
      $userId = $validated['id_mail'];
      
        $mail = Mailings::where('idUser', $userId)->first();
        if ($mail) {
            $mail->update([
                'isValidated' => true,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Post validé avec succès',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post non trouvé',
                'status' => 404,
            ], 404);
        }
    }
  
    public function publishedMail(Request $request){
    $validated =  $request->validate([
        'id_mail' => 'required|integer',
    ]);
      $userId = $validated['id_mail'];
     
        $mail = Mailings::where('idUser', $userId)->first();
        if ($mail) {
            $mail->update([
                'isPublished' => true,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Post publié avec succès',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post non trouvé',
                'status' => 404,
            ], 404);
        }
    }
}
