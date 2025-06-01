<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Models\clients;
use App\Models\Mailings;
use App\Models\ClientsMailings;
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
    ]);
    try {
         $to = $request->input('to');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $altBody = $request->input('altBody', '');
        $fromName = $request->input('fromName', 'WIZIA');
        $fromEmail = $request->input('fromEmail', 'contact@dimitribeziau.fr');

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

        if (!$mail->send()) {
          throw new \Exception("Échec de l'envoi à $destinataire : " . $mail->ErrorInfo);
        }
      }

        return response()->json(['message' => 'Email(s) envoyé(s) avec succès','success' => true], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(),'success' => false], 500);
    }
  }



  public function addAttachment($filePath, $fileName)
  {
    $this->mail->addAttachment($filePath, $fileName);
  }



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
      ]);

      $mail = new Mailings();
      $mail->idUser  = $idUser;
      $mail->subject = $validated['subject'];
      $mail->body = $validated['body'];
      $mail->altBody = $validated['altBody'] ?? null;
      $mail->fromName = $validated['fromName'] ?? null;
      $mail->fromEmail = $validated['fromEmail'] ?? null;
      $mail->save();

      foreach ($validated['toListId'] as $destId) {
        $ClientsMailings = new ClientsMailings();
        $ClientsMailings->idMailing = $mail->id;
        $ClientsMailings->idClient = $destId;
        $ClientsMailings->save();
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
}
