<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\ClientsMailings;
use App\Models\Mailings;
use App\Models\PieceJointeMailings;
use App\Models\PieceJointes;
use Dotenv\Dotenv;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class C_MailController extends Controller
{
    private const LOCALHOST = '127.0.0.1';
    private const REQ_STRING = 'required|string';
    private const REQ_ARRAY = 'required|array';
    private const NUL_STRING = 'nullable|string';
    private const NUL_EMAIL = 'nullable|email';
    private const NUL_ARRAY = 'nullable|array';
    private const NUL_BOOL = 'nullable|boolean';
    private const FILE_STAR = 'file.*';
    private const DEF_EMAIL = 'dimitri@beziau.dev';
    private const ERR_ID_INVALID = 'ID invalide';
    private const ERR_MAIL_NOT_FOUND = 'Mailing non trouvé';

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
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->Host = config('mail.mailers.smtp.host', self::LOCALHOST);
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
        $outputBuffer = '';

        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
            $mail->Debugoutput = function ($str) use (&$outputBuffer) {
                $outputBuffer .= "$str\n";
            };

            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host', self::LOCALHOST);
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

            $mail->setFrom(config('mail.from.address', 'test@example.com'), 'Test');
            $mail->addAddress(config('mail.from.address', 'test@example.com')); // Send to self
            $mail->Subject = 'SMTP Test';
            $mail->Body = 'This is a test email';

            if ($mail->smtpConnect()) {
                $mail->smtpClose();

                return response()->json([
                    'success' => true,
                    'message' => 'SMTP Connect Successful!',
                    'config_check' => [
                        'host' => $mail->Host,
                        'port' => $mail->Port,
                        'encryption' => $encryption,
                        'username_set' => ! empty($mail->Username),
                        'username_len' => strlen($mail->Username),
                        'password_set' => ! empty($mail->Password),
                        'password_len' => strlen($mail->Password),
                    ],
                    'log' => $outputBuffer,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SMTP Connect Failed',
                    'config_check' => [
                        'host' => $mail->Host,
                        'port' => $mail->Port,
                        'encryption' => $encryption,
                        'username_set' => ! empty($mail->Username),
                        'username_len' => strlen($mail->Username),
                        'password_set' => ! empty($mail->Password),
                        'password_len' => strlen($mail->Password),
                    ],
                    'log' => $outputBuffer,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
                'log' => $outputBuffer,
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/mail/generateMail",
     *     summary="Envoie un email avec PHPMailer",
     *     tags={"Mailing"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"to", "subject", "body"},
     *
     *                 @OA\Property(property="to", type="array", @OA\Items(type="string", format="email"),
     *                     example={"john@example.com", "jane@example.com"}),
     *                 @OA\Property(property="subject", type="string", example="Votre commande"),
     *                 @OA\Property(property="body", type="string",
     *                     example="<h1>Bonjour</h1><p>Merci pour votre commande</p>"),
     *                 @OA\Property(property="altBody", type="string", example="Version texte de l'email"),
     *                 @OA\Property(property="fromName", type="string", example="WIZIA"),
     *                 @OA\Property(property="fromEmail", type="string", format="email",
     *                     example="contact@wizia.com"),
     *                 @OA\Property(property="file", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="idMailing", type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email envoyé avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Email(s) envoyé(s) avec succès"),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de l'envoi",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function generateMail(Request $request)
    {

        $request->validate([
            'to' => self::REQ_ARRAY,
            'to.*' => 'email',
            'subject' => self::REQ_STRING,
            'body' => self::REQ_STRING,
            'altBody' => self::NUL_STRING,
            'fromName' => self::NUL_STRING,
            'fromEmail' => self::NUL_EMAIL,
            'file' => self::NUL_ARRAY,
            self::FILE_STAR => 'string',

        ]);
        try {
            $to = $request->input('to');
            $subject = $request->input('subject');
            $body = $request->input('body');
            $altBody = $request->input('altBody', '');
            $fromName = $request->input('fromName', 'WIZIA');
            $fromEmail = $request->input('fromEmail', self::DEF_EMAIL);
            $file = $request->input('file');

            foreach ($to as $destinataire) {

                // Use a fresh instance instead of cloning to ensure clean state
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = config('mail.mailers.smtp.host', self::LOCALHOST);
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

                if (! $mail->send()) {
                    throw new \RuntimeException("Échec de l'envoi à $destinataire : ".$mail->ErrorInfo);
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
            'to' => self::REQ_ARRAY,
            'to.*' => 'email',
            'subject' => self::REQ_STRING,
            'body' => self::REQ_STRING,
            'altBody' => self::NUL_STRING,
            'fromName' => self::NUL_STRING,
            'fromEmail' => self::NUL_EMAIL,
            'file' => self::NUL_ARRAY,
            self::FILE_STAR => 'string',
            'idMailing' => 'nullable|integer',
            'now' => self::NUL_BOOL,
            'isValidated' => 'nullable|integer|in:0,1',
            'dateMail' => 'nullable|date',
            'idUser' => 'nullable|integer',
        ]);

        try {
            if ($request->input('now') == true) {
                return $this->processImmediatePublish($request);
            }

            return $this->processScheduledPublish($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function processImmediatePublish(Request $request)
    {
        $to = $request->input('to');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $altBody = $request->input('altBody', '');
        $fromName = $request->input('fromName', 'WIZIA');
        $fromEmail = $request->input('fromEmail', self::DEF_EMAIL);
        $file = $request->input('file');
        $idMail = $request->input('idMailing', null);
        $isValidated = $request->input('isValidated');
        $dateMail = $request->input('dateMail');
        $idUser = $request->input('idUser');

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
                'success' => false,
                'message' => "Erreur lors de l'envoi du mail",
                'error' => $response->getData(true)['error'] ?? 'Erreur inconnue',
            ], 500);
        }

        $isValidatedBool = ($isValidated == 0) ? false : true;

        $reqdestinataireId = new Request([
            'mail' => $to,
            'idUser' => $idUser,
        ]);
        $toListIdResponse = $this->getListDestinataireEmail($reqdestinataireId);
        $toListId = $toListIdResponse->getData(true)['data'];

        if ($idMail !== null) {
            $reqUpdate = new \Illuminate\Http\Request([
                'idMailing' => $idMail,
                'to' => $to,
                'toListId' => $toListId,
                'subject' => $subject,
                'body' => $body,
                'altBody' => $altBody,
                'fromEmail' => $fromEmail,
                'fromName' => $fromName,
                'date' => $dateMail,
                'file' => $file,
                'isValidated' => $isValidatedBool,
            ]);
            $mailResponse = $this->updateMailing($reqUpdate, $idUser);
            $mailData = $mailResponse->getData(true);
            $mailId = $mailData['data']['id'] ?? null;
        } else {
            $reqAdd = new \Illuminate\Http\Request([
                'to' => $to,
                'toListId' => $toListId,
                'idMailing' => $idMail,
                'subject' => $subject,
                'body' => $body,
                'altBody' => $altBody,
                'fromEmail' => $fromEmail,
                'fromName' => $fromName,
                'date' => $dateMail,
                'file' => $file,
                'isValidated' => $isValidatedBool,
                'isPublished' => false,
            ]);
            $mailResponse = $this->AddMail($reqAdd, $idUser);
            $mailData = $mailResponse->getData(true);
            $mailId = $mailData['id'] ?? null;
        }

        if (! $mailData['success']) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de l'ajout du mail",
                'error' => $mailData['message'] ?? 'Erreur inconnue',
            ], 500);
        }

        if ($mailId) {
            $this->publishedMail($mailId);
        }

        $isSuccess = ($response->getStatusCode() == 200);

        return response()->json([
            'success' => $isSuccess,
            'status' => $response->getStatusCode(),
            'message' => $isSuccess ? 'Mail envoyé & enregistré avec succès' : "Erreur lors de l'envoi de mail",
            'idMailing' => $mailId,
            'responseData' => $response->getData(true),
        ]);
    }

    private function processScheduledPublish(Request $request)
    {
        $to = $request->input('to');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $altBody = $request->input('altBody', '');
        $fromName = $request->input('fromName', 'WIZIA');
        $fromEmail = $request->input('fromEmail', self::DEF_EMAIL);
        $file = $request->input('file');
        $idMail = $request->input('idMailing', null);
        $isValidated = $request->input('isValidated');
        $dateMail = $request->input('dateMail');
        $idUser = $request->input('idUser');

        $reqdestinataireId = new Request([
            'mail' => $to,
            'idUser' => $idUser,
        ]);
        $toListIdResponse = $this->getListDestinataireEmail($reqdestinataireId);
        $toListId = $toListIdResponse->getData(true)['data'];

        $isValidatedBool = ($isValidated == 0) ? false : true;

        $reqAddLater = new \Illuminate\Http\Request([
            'idMailing' => $idMail,
            'subject' => $subject,
            'body' => $body,
            'altBody' => $altBody,
            'fromEmail' => $fromEmail,
            'fromName' => $fromName,
            'date' => $dateMail ?? now(),
            'file' => $file,
            'isValidated' => $isValidatedBool,
            'to' => $to,
            'toListId' => $toListId,
            'isPublished' => false,
        ]);

        if ($idMail) {
            return $this->updateMailing($reqAddLater, $idUser);
        } else {
            return $this->AddMail($reqAddLater, $idUser);
        }
    }

    /**
     * @OA\Post(
     *     path="/mail/AddMail/{idUser}",
     *     summary="Ajoute un mailing en base de données",
     *     tags={"Mailing"},
     *
     *     @OA\Parameter(
     *         name="idUser",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="ID de l'utilisateur"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"to", "toListId", "subject", "body"},
     *
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
     *
     *     @OA\Response(
     *         response=200,
     *         description="Mail ajouté avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="ID invalide"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function AddMail(Request $request, $idUser)
    {
        try {
            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_ID_INVALID,
                ], 400);
            }

            $validated = $request->validate([
                'to' => self::REQ_ARRAY,
                'to.*' => 'email',
                'toListId' => self::NUL_ARRAY,
                'toListId.*' => 'integer',
                'subject' => self::REQ_STRING,
                'body' => self::REQ_STRING,
                'altBody' => self::NUL_STRING,
                'fromName' => self::NUL_STRING,
                'fromEmail' => self::NUL_EMAIL,
                'file' => self::NUL_ARRAY,
                self::FILE_STAR => 'string',
                'date' => 'nullable|date',
                'isValidated' => self::NUL_BOOL,
                'isPublished' => self::NUL_BOOL,
            ]);

            // Créer le mailing
            $mail = new Mailings;
            $mail->idUser = $idUser;
            $mail->subject = $validated['subject'];
            $mail->body = $validated['body'];
            $mail->altBody = $validated['altBody'] ?? null;
            $mail->fromName = $validated['fromName'] ?? 'Wizia';
            $mail->fromEmail = $validated['fromEmail'] ?? self::DEF_EMAIL;
            $mail->isPublished = $validated['isPublished'] ?? false;
            $mail->isValidated = $validated['isValidated'] ?? false;
            $mail->date = $validated['date'] ?? date('Y-m-d H:i:s');
            $mail->save();

            // Lier les clients au mailing
            foreach ($validated['toListId'] as $destId) {
                $clientsMailing = new ClientsMailings;
                $clientsMailing->idMailing = $mail->id;
                $clientsMailing->idListeClient = $destId;
                $clientsMailing->save();
            }

            // Gestion des fichiers
            if (isset($validated['file']) && is_array($validated['file'])) {
                foreach ($validated['file'] as $file) {
                    $pieceJointe = new PieceJointes;

                    // Si $file est une URL, on tente de récupérer le type MIME via HTTP headers
                    if (filter_var($file, FILTER_VALIDATE_URL)) {
                        $headers = get_headers($file, 1);
                        if (isset($headers['Content-Type'])) {
                            $mimeType = is_array($headers['Content-Type'])
                                ? $headers['Content-Type'][0]
                                : $headers['Content-Type'];
                        } else {
                            $mimeType = null;
                        }
                        // Si on n'a pas le type, fallback sur extension
                        if (! $mimeType) {
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

                    $pieceJointeMailing = new PieceJointeMailings;
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

    public function mime_content_type_from_extension($ext)
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
     *
     *     @OA\Parameter(
     *         name="idMailing",
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
     *             required={"subject", "body"},
     *
     *             @OA\Property(property="subject", type="string"),
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="altBody", type="string"),
     *             @OA\Property(property="fromName", type="string"),
     *             @OA\Property(property="fromEmail", type="string", format="email"),
     *             @OA\Property(property="isValidated", type="boolean"),
     *             @OA\Property(property="isPublished", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Mailing mis à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Mailing non trouvé")
     * )
     */

    // Mettre à jour un mailing
    public function updateMailing(Request $request)
    {
        try {
            $validated = $request->validate([
                'idMailing' => 'required|integer|exists:mailings,id',
                'subject' => 'required|string|max:255',
                'body' => self::REQ_STRING,
                'altBody' => self::NUL_STRING,
                'fromName' => self::NUL_STRING,
                'fromEmail' => self::NUL_EMAIL,
                'isValidated' => self::NUL_BOOL,
                'isPublished' => self::NUL_BOOL,
                'toListId' => self::NUL_ARRAY,
                'toListId.*' => 'integer',
                'file' => self::NUL_ARRAY,
                self::FILE_STAR => 'string', // URL publique
            ]);

            $mailing = Mailings::findOrFail($validated['idMailing']);

            // --- Mise à jour du mailing ---
            $mailing->subject = $validated['subject'];
            $mailing->body = $validated['body'];
            $mailing->altBody = $validated['altBody'] ?? $mailing->altBody;
            $mailing->fromName = $validated['fromName'] ?? $mailing->fromName;
            $mailing->fromEmail = $validated['fromEmail'] ?? $mailing->fromEmail;
            $mailing->isValidated = $validated['isValidated'] ?? $mailing->isValidated;
            $mailing->isPublished = $validated['isPublished'] ?? $mailing->isPublished;
            $mailing->date = now();
            $mailing->save();

            // --- Listes de destinataires ---
            if (! empty($validated['toListId'])) {
                foreach ($validated['toListId'] as $destId) {
                    ClientsMailings::firstOrCreate([
                        'idMailing' => $mailing->id,
                        'idListeClient' => $destId,
                    ]);
                }
            }

            // --- Pièces jointes (URL publiques) ---
            if (! empty($validated['file'])) {
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
                        'path' => $fileUrl,
                        'type' => 'url',
                        'idUser' => $mailing->idUser,
                    ]);

                    PieceJointeMailings::create([
                        'idMailing' => $mailing->id,
                        'idPieceJointe' => $pieceJointe->id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Mailing mis à jour avec succès',
                'data' => $mailing,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mailing',
                'error' => $e->getMessage(),
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
                'data' => $mailings,
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
     *     path="/mail/ListMailingUser/{idUser}",
     *     summary="Récupère tous les mailings d'un utilisateur",
     *     tags={"Mailing"},
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
     *         description="Liste des mailings",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getListMailingUser($idUser)
    {
        try {
            if (! is_numeric($idUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID utilisateur invalide',
                ], 400);
            }

        $mailings = Mailings::with('files')->where('idUser', $idUser)->get();
        foreach ($mailings as $mailing) {
            $mailing->file = $mailing->files->pluck('path')->toArray();
            unset($mailing->files);
        }

        return response()->json([
            'success' => true,
            'data' => $mailings->isEmpty() ? [] : $mailings,
            'paths' => $mailings->isEmpty() ? [] : null, // keep the same structure
        ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des mailings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/mail/ListMailingsendClient/{idMail}",
     *     summary="Récupère un mailing avec ses destinataires",
     *     tags={"Mailing"},
     *
     *     @OA\Parameter(
     *         name="idMail",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Mailing avec destinataires",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mailing", type="object"),
     *                 @OA\Property(property="clients", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Mailing non trouvé")
     * )
     */
    public function getListMailingWhithSendClients($idMail)
    {
        try {
            // Vérification que l'ID est bien numérique
            if (! ctype_digit((string) $idMail)) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_ID_INVALID,
                ], 400);
            }

            // Récupération du mailing
            $mailing = Mailings::find($idMail);
            if (! $mailing) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_MAIL_NOT_FOUND,
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
                    'clients' => $clients,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du mailing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/mail/SearchMailing/{idMailing}",
     *     summary="Récupère un mailing par son ID",
     *     tags={"Mailing"},
     *
     *     @OA\Parameter(
     *         name="idMailing",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détails du mailing",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Mailing non trouvé")
     * )
     */
    public function SearchMailingById($idMailing)
    {
        try {
            if (! ctype_digit((string) $idMailing)) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_ID_INVALID,
                ], 400);
            }

            $mailing = Mailings::find($idMailing);
            if (! $mailing) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_MAIL_NOT_FOUND,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $mailing,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du mailing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/mail/DeleteMailing/{idMailing}",
     *     summary="Supprime un mailing",
     *     tags={"Mailing"},
     *
     *     @OA\Parameter(
     *         name="idMailing",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Mailing supprimé",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mailing supprimé avec succès")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Mailing non trouvé",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    // Supprimer un mailing
    public function deleteMailing($idMailing)
    {
        try {
            if (! ctype_digit((string) $idMailing)) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_ID_INVALID,
                ], 400);
            }

            $mailing = Mailings::find($idMailing);
            if (! $mailing) {
                return response()->json([
                    'success' => false,
                    'message' => self::ERR_MAIL_NOT_FOUND,
                ], 404);
            }

            $mailing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mailing supprimé avec succès',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du mailing',
                'error' => $e->getMessage(),
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
        }

        return response()->json([
            'success' => false,
            'message' => 'Post non trouvé',
            'status' => 404,
        ], 404);
    }

    public function publishedMail($mailId)
    {

        $mail = Mailings::find($mailId);

        if (! $mail) {
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
