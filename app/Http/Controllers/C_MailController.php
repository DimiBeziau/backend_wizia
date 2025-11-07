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
 public function getMailingById($idMailing)
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
