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
    // $dotenv = Dotenv::createImmutable(base_path());
    // $dotenv->load();

    $this->mail->isSMTP();
    $this->mail->SMTPAuth = true;
    $this->mail->Host = config('mail.mailers.smtp.host', '127.0.0.1');
    $this->mail->Port = config('mail.mailers.smtp.port', 2525);
    $this->mail->Username = config('mail.mailers.smtp.username');
    $this->mail->Password = config('mail.mailers.smtp.password');


    $encryption = config('mail.mailers.smtp.encryption', 'tls');
    if ($encryption === 'ssl') {
      $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
      $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
  }
  public function addAttachment($mail, $content, $fileName)
  {
    $mail->addStringAttachment($content, $fileName);
  }

  public function testSmtp()
  {
    $outputBuffer = "";

    try {
      $mail = new PHPMailer(true);
      $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
      $mail->Debugoutput = function ($str, $level) use (&$outputBuffer) {
        $outputBuffer .= "$str\n";
      };

      $mail->isSMTP();
      $mail->Host = config('mail.mailers.smtp.host', '127.0.0.1');
      $mail->Port = config('mail.mailers.smtp.port', 2525);
      $mail->SMTPAuth = true;
      $mail->Username = config('mail.mailers.smtp.username');
      $mail->Password = config('mail.mailers.smtp.password');

      $encryption = config('mail.mailers.smtp.encryption', 'tls');
      if ($encryption === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      }

      // OPTIONAL: Disable certificate verification for diagnosing self-signed cert issues
      /*
      $mail->SMTPOptions = array(
          'ssl' => array(
              'verify_peer' => false,
              'verify_peer_name' => false,
              'allow_self_signed' => true
          )
      );
      */

      $mail->setFrom(config('mail.from.address', 'test@example.com'), 'Test');
      $mail->addAddress(config('mail.from.address', 'test@example.com')); // Send to self
      $mail->Subject = 'SMTP Test';
      $mail->Body    = 'This is a test email';

      if ($mail->smtpConnect()) {
        $mail->smtpClose();
        return response()->json([
          'success' => true,
          'message' => "SMTP Connect Successful!",
          'config_check' => [
            'host' => $mail->Host,
            'port' => $mail->Port,
            'encryption' => $encryption,
            'username_set' => !empty($mail->Username),
            'username_len' => strlen($mail->Username),
            'password_set' => !empty($mail->Password),
            'password_len' => strlen($mail->Password),
          ],
          'log' => $outputBuffer
        ]);
      } else {
        return response()->json([
          'success' => false,
          'message' => "SMTP Connect Failed",
          'config_check' => [
            'host' => $mail->Host,
            'port' => $mail->Port,
            'encryption' => $encryption,
            'username_set' => !empty($mail->Username),
            'username_len' => strlen($mail->Username),
            'password_set' => !empty($mail->Password),
            'password_len' => strlen($mail->Password),
          ],
          'log' => $outputBuffer
        ], 500);
      }
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => "Exception: " . $e->getMessage(),
        'log' => $outputBuffer
      ], 500);
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
      'file.*' => 'string',

    ]);
    try {
      $to = $request->input('to');
      $subject = $request->input('subject');
      $body = $request->input('body');
      $altBody = $request->input('altBody', '');
      $fromName = $request->input('fromName', 'WIZIA');
      $fromEmail = $request->input('fromEmail', 'dimitri@beziau.dev');
      $file = $request->input('file');


      foreach ($to as $destinataire) {

        // Use a fresh instance instead of cloning to ensure clean state
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = config('mail.mailers.smtp.host', '127.0.0.1');
        $mail->Port = config('mail.mailers.smtp.port', 2525);
        $mail->SMTPAuth = true;
        $mail->Username = config('mail.mailers.smtp.username');
        $mail->Password = config('mail.mailers.smtp.password');

        $encryption = config('mail.mailers.smtp.encryption', 'tls');
        if ($encryption === 'ssl') {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($destinataire);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        if ($file) {
          if (is_array($file)) {
            foreach ($file as $url) {
              $content = @file_get_contents($url);
              if ($content !== false) {
                $this->addAttachment($mail, $content, basename($url));
              }
            }
          } else {
            $content = @file_get_contents($file);
            if ($content !== false) {
              $this->addAttachment($mail, $content, basename($file));
            }
          }
        }

        if (!$mail->send()) {
          throw new \Exception("Échec de l'envoi à $destinataire : " . $mail->ErrorInfo);
        }
      }

      return response()->json(['message' => 'Email(s) envoyé(s) avec succès', 'success' => true], 200);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage(), 'success' => false], 500);
    }
  }

  public function createPublishMail(Request $request)
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
      'file.*' => 'string',
      'idMailing' => 'nullable|integer',
      'now' => 'nullable|boolean',
      'isValidated' => 'nullable|integer|in:0,1',
      'dateMail' => 'nullable|date',
      'idUser' => 'nullable|integer',
    ]);

    try {
      $to = $request->input('to');
      $subject = $request->input('subject');
      $body = $request->input('body');
      $altBody = $request->input('altBody', '');
      $fromName = $request->input('fromName', 'WIZIA');
      $fromEmail = $request->input('fromEmail', 'dimitri@beziau.dev');
      $file = $request->input('file');
      $idMail = $request->input('idMailing', null);
      $isValidated = $request->input('isValidated');
      $dateMail = $request->input('dateMail');
      $now = $request->input('now');
      $idUser = $request->input('idUser');

      if ($now == true) {
        $reqMail = clone $request;
        $reqMail->replace([
          'to' => $to,
          'subject' => $subject,
          'body' => $body,
          'altBody' => $altBody,
          'fromName' => $fromName,
          'fromEmail' => $fromEmail,
          'file' => $file,
        ]);

        $response = $this->generateMail($reqMail);


        if ($response->getStatusCode() != 200) {
          return response()->json([
            "success" => false,
            "message" => "Erreur lors de l'envoi du mail",
            "error" => $response->getData(true)['error'] ?? 'Erreur inconnue',
          ], 500);
        }

        if ($idMail !== null) {

          $reqdestinataireId = new Request([
            "mail" => $to,
            "idUser" => $idUser,

          ]);
          $toListIdResponse = $this->getListDestinataireEmail($reqdestinataireId);
          $toListId =  $toListIdResponse->getData(true)['data'];

          $isValidatedBool = ($isValidated == 0) ? false : true;

          $reqUpdate = new Request([
            "idMailing" => $idMail,
            'to' => $to,
            'toListId' => $toListId,
            "subject" => $subject,
            "body" => $body,
            "altBody" => $altBody,
            "fromEmail" => $fromEmail,
            "fromName" => $fromName,
            "date" => $dateMail,
            "file" => $file,
            "isValidated" => $isValidatedBool,
          ]);

          $mailResponse = $this->updateMailing($reqUpdate, $idUser);
        } else {
          $reqdestinataireId = new Request([
            "mail" => $to,
            "idUser" => $idUser,

          ]);
          $toListIdResponse = $this->getListDestinataireEmail($reqdestinataireId);

          $toListId =  $toListIdResponse->getData(true)['data'];

          $isValidatedBool = ($isValidated == 0) ? false : true;

          $reqAdd = new Request([
            'to' => $to,
            'toListId' => $toListId,
            "idMailing" => $idMail,
            "subject" => $subject,
            "body" => $body,
            "altBody" => $altBody,
            "fromEmail" => $fromEmail,
            "fromName" => $fromName,
            "date" => $dateMail,
            "file" => $file,
            "isValidated" => $isValidatedBool,
            "isPublished" => false,
          ]);

          $mailResponse = $this->AddMail($reqAdd, $idUser);
        }

        if ($idMail !== null) {
          $mailData = $mailResponse->getData(true);

          $mailId = $mailData['data']['id'] ?? null;
        } else {
          $mailData = $mailResponse->getData(true);
          $mailId = $mailData['id'] ?? null;
        }
        if (!$mailData['success']) {
          return response()->json([
            "success" => false,
            "message" => "Erreur lors de l'ajout du mail",
            "error" => $mailData['message'] ?? 'Erreur inconnue',
          ], 500);
        }



        if ($mailId) {
          $this->publishedMail($mailId);
        }


        $isSuccess = ($response->getStatusCode() == 200);

        return response()->json([
          "success" => $isSuccess,
          "status" => $response->getStatusCode(),
          "message" => $isSuccess
            ? "Mail envoyé & enregistré avec succès"
            : "Erreur lors de l'envoi de mail",
          "idMailing" => $mailId,
          "responseData" => $response->getData(true), // Au lieu de json()
        ]);
      }


      $reqdestinataireId = new Request([
        "mail" => $to,
        "idUser" => $idUser,

      ]);
      $toListIdResponse = $this->getListDestinataireEmail($reqdestinataireId);
      $toListId =  $toListIdResponse->getData(true)['data'];

      $isValidatedBool = ($isValidated == 0) ? false : true;

      $reqAddLater = new Request([
        "idMailing" => $idMail,
        "subject" => $subject,
        "body" => $body,
        "altBody" => $altBody,
        "fromEmail" => $fromEmail,
        "fromName" => $fromName,
        "date" => $dateMail ?? now(),
        "file" => $file,
        "isValidated" => $isValidatedBool,
        "to" => $to,
        "toListId" => $toListId,
        "isPublished" => false,
      ]);

      if ($idMail) {
        return $this->updateMailing($reqAddLater, $idUser);
      } else {
        return $this->AddMail($reqAddLater, $idUser);
      }
    } catch (\Exception $e) {
      return response()->json([
        'error' => $e->getMessage(),
        'success' => false,
        'line' => $e->getLine(),
      ], 500);
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
        'toListId' => 'nullable|array',
        'toListId.*' => 'integer',
        'subject' => 'required|string',
        'body' => 'required|string',
        'altBody' => 'nullable|string',
        'fromName' => 'nullable|string',
        'fromEmail' => 'nullable|email',
        'file' => 'nullable|array',
        'file.*' => 'string',
        'date' => 'nullable|date',
        'isValidated' => 'nullable|boolean',
        'isPublished' => 'nullable|boolean',
      ]);

      // Créer le mailing
      $mail = new Mailings();
      $mail->idUser = $idUser;
      $mail->subject = $validated['subject'];
      $mail->body = $validated['body'];
      $mail->altBody = $validated['altBody'] ?? null;
      $mail->fromName = $validated['fromName'] ?? "Wizia";
      $mail->fromEmail = $validated['fromEmail'] ?? "dimitri@beziau.dev";
      $mail->isPublished = $validated['isPublished'] ?? false;
      $mail->isValidated = $validated['isValidated'] ?? false;
      $mail->date = $validated['date'] ?? date('Y-m-d H:i:s');
      $mail->save();

      // Lier les clients au mailing
      foreach ($validated['toListId'] as $destId) {
        $clientsMailing = new ClientsMailings();
        $clientsMailing->idMailing = $mail->id;
        $clientsMailing->idListeClient = $destId;
        $clientsMailing->save();
      }

      // Gestion des fichiers
      if (isset($validated['file']) && is_array($validated['file'])) {
        foreach ($validated['file'] as $file) {
          $pieceJointe = new PieceJointes();

          // Si $file est une URL, on tente de récupérer le type MIME via HTTP headers
          if (filter_var($file, FILTER_VALIDATE_URL)) {
            $headers = get_headers($file, 1);
            $mimeType = isset($headers['Content-Type']) ? (is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type']) : null;
            // Si on n'a pas le type, fallback sur extension
            if (!$mimeType) {
              $ext = pathinfo(parse_url($file, PHP_URL_PATH), PATHINFO_EXTENSION);
              $mimeType = $ext ? $this->mime_content_type_from_extension($ext) : null;
            }
          } else {
            // Sinon, on tente localement
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file);
            finfo_close($finfo);
          }

          $pieceJointe->type = $mimeType;
          $pieceJointe->idUser = $idUser;
          $pieceJointe->path = $file; // On stocke bien l'URL ou le chemin

          $pieceJointe->save();

          $pieceJointeMailing = new PieceJointeMailings();
          $pieceJointeMailing->idPieceJointe = $pieceJointe->id;
          $pieceJointeMailing->idMailing = $mail->id;
          $pieceJointeMailing->save();
        }
      }

      return response()->json([
        'success' => true,
        'message' => 'Mail ajouté avec succès',
        'id' => $mail->id,
        'user' => $idUser,
        'mail' => $mail,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de l\'ajout du mail',
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
      ], 500);
    }
  }

  function mime_content_type_from_extension($ext)
  {
    $map = [
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
      'gif' => 'image/gif',
      'pdf' => 'application/pdf',
      // Ajoute d'autres extensions si besoin
    ];
    $ext = strtolower($ext);
    return $map[$ext] ?? 'application/octet-stream';
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
  public function updateMailing(Request $request, $iduser)
  {
    try {
      $validated = $request->validate([
        'idMailing'   => 'required|integer|exists:mailings,id',
        'subject'     => 'required|string|max:255',
        'body'        => 'required|string',
        'altBody'     => 'nullable|string',
        'fromName'    => 'nullable|string',
        'fromEmail'   => 'nullable|email',
        'isValidated' => 'nullable|boolean',
        'isPublished' => 'nullable|boolean',
        'toListId'    => 'nullable|array',
        'toListId.*'  => 'integer',
        'file'        => 'nullable|array',
        'file.*'      => 'string' // URL publique
      ]);

      $mailing = Mailings::findOrFail($validated['idMailing']);

      // --- Mise à jour du mailing ---
      $mailing->subject     = $validated['subject'];
      $mailing->body        = $validated['body'];
      $mailing->altBody     = $validated['altBody']     ?? $mailing->altBody;
      $mailing->fromName    = $validated['fromName']    ?? $mailing->fromName;
      $mailing->fromEmail   = $validated['fromEmail']   ?? $mailing->fromEmail;
      $mailing->isValidated = $validated['isValidated'] ?? $mailing->isValidated;
      $mailing->isPublished = $validated['isPublished'] ?? $mailing->isPublished;
      $mailing->date        = now();
      $mailing->save();

      // --- Listes de destinataires ---
      if (!empty($validated['toListId'])) {
        foreach ($validated['toListId'] as $destId) {
          ClientsMailings::firstOrCreate([
            'idMailing'      => $mailing->id,
            'idListeClient'  => $destId,
          ]);
        }
      }

      // --- Pièces jointes (URL publiques) ---
      if (!empty($validated['file'])) {
        foreach ($validated['file'] as $fileUrl) {

          $exists = PieceJointeMailings::where('idMailing', $mailing->id)
            ->whereHas('pieceJointe', function ($q) use ($fileUrl) {
              $q->where('path', $fileUrl);
            })
            ->exists();

          if ($exists) {
            continue;
          }

          $pieceJointe = PieceJointes::create([
            'path'   => $fileUrl,
            'type'   => 'url',
            'idUser' => $mailing->idUser
          ]);

          PieceJointeMailings::create([
            'idMailing'     => $mailing->id,
            'idPieceJointe' => $pieceJointe->id
          ]);
        }
      }

      return response()->json([
        'success' => true,
        'message' => 'Mailing mis à jour avec succès',
        'data'    => $mailing
      ], 200);
    } catch (\Throwable $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du mailing',
        'error'   => $e->getMessage(),
      ], 500);
    }
  }



  public function crudUpdateMailing()
  {
    try {
      // Récupérer tous les mailings non publiés
      $mailings = Mailings::where('isPublished', false)->get();
      //   $mailings = Mailingsclient::where('isPublished', false)->get();

      foreach ($mailings as $mailing) {
        // Préparer les données pour createPublishMail
        $requestData = [
          'to' => $mailing->clients()->pluck('mail')->toArray(), // emails destinataires
          'subject' => $mailing->subject,
          'body' => $mailing->body,
          'altBody' => $mailing->altBody,
          'fromName' => $mailing->fromName,
          'fromEmail' => $mailing->fromEmail,
          'file' => [], // si tu as des fichiers attachés, les ajouter ici
          'idMailing' => $mailing->id,
          'now' => true, // si tu veux envoyer maintenant
          'isValidated' => $mailing->isValidated ? 1 : 0,
          'dateMail' => now(),
          'idUser' => $mailing->idUser,
        ];

        $req = new \Illuminate\Http\Request($requestData);

        // Appeler la fonction pour envoyer le mail
        $this->createPublishMail($req);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tous les mailings non publiés ont été traités',
        'data' => $mailings
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération ou de l’envoi des mailings',
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
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
        'data' => [$clients]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération des destinataires',
        'error' => $e->getMessage()
      ], 500);
    }
  }
  // Récupération des destinataires par email et idUser
  public function getListDestinataireEmail(Request $request)
  {
    try {
      $emails = $request->input('mail', []);
      $idUser = $request->input('idUser');

      if (!is_numeric($idUser)) {
        return response()->json([
          'success' => false,
          'message' => 'ID invalide'
        ], 400);
      }

      $clients = Clients::where('idUser', $idUser)
        ->whereIn('mail', $emails)
        ->get(['id', 'mail']);

      // On renvoie directement les IDs pour simplifier l'utilisation
      $ids = $clients->pluck('id')->toArray();

      return response()->json([
        'success' => true,
        'data' => $ids
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


  public function ListDestinatairebyMail($mailDestinataire)
  {
    try {
      $client = clients::where('mail', $mailDestinataire)->first();

      if (!$client) {
        return response()->json([
          'success' => false,
          'message' => 'Destinataire non trouvé'
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => $client
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

      // Récupération des mailings de l'utilisateur
      $mailings = Mailings::with('files')->where('idUser', $idUser)->get();
      foreach ($mailings as $mailing) {
        $mailing->file = $mailing->files->pluck('path')->toArray();
        unset($mailing->files);
      }

      if ($mailings->isEmpty()) {
        return response()->json([
          'success' => true,
          'data' => [],
          'paths' => []
        ], 200);
      }



      return response()->json([
        'success' => true,
        'data' => $mailings,

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
      $clients = Clients::whereIn('id', function ($query) use ($idMail) {
        $query->select('idListeClient')
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

  public function validatedMail($mailId)
  {

    $mail = Mailings::find($mailId);
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

  public function publishedMail($mailId)
  {

    $mail = Mailings::find($mailId);

    if (!$mail) {
      return response()->json([
        'success' => false,
        'message' => 'mail non trouvé',
        'status' => 404,
      ], 404);
    }
    $mail->update(['isPublished' => true]);
    return response()->json([
      'success' => true,
      'message' => 'mail publié avec succès',
      'status' => 200,
    ], 200);
  }
}
