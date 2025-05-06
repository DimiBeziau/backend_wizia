<?php

// namespace App\Http\Controllers;
namespace App\Services;
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// require('../../../vendor/autoload.php');
// use PHPMailer\PHPMailer\Exception;
// use Dotenv\Dotenv;
// use Illuminate\Support\Facades\DB;

 
// class MailService 
// {
//     private $mail;

//     public function __construct($debug = false)
//     {
//         $this->mail = new PHPMailer($debug);
//         if ($debug) {
//             $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
//         }
//         $this->configureSMTP();
//     }

//     private function configureSMTP()
//     {
//         $dotenv = Dotenv::createImmutable(base_path());
//         $dotenv->load();

//         $this->mail->isSMTP();
//         $this->mail->SMTPAuth = true;
//         $this->mail->Host = env('MAIL_HOST');
//         $this->mail->Port = env('MAIL_PORT');
//         $this->mail->Username = env('MAIL_USERNAME');
//         $this->mail->Password = env('MAIL_PASSWORD');
//         $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//     }

//     public function sendEmail($to, $subject, $body, $altBody = '', $fromName = 'nom', $fromEmail = 'contact@dimitribeziau.fr')
//     {
//         try {
//             $this->mail->setFrom($fromEmail, $fromName);
//             $this->mail->addAddress($to);
//             $this->mail->CharSet = 'UTF-8';
//             $this->mail->Encoding = 'base64';
//             $this->mail->isHTML(true);
//             $this->mail->Subject = $subject;
//             $this->mail->Body = $body;
//             $this->mail->AltBody = $altBody;
//             return $this->mail->send();
//         } catch (Exception $e) {
//             echo "Message could not be sent. Mailer Error: " . $this->mail->ErrorInfo;
//             return false;
//         }
//     }

//     public function addAttachment($filePath, $fileName)
//     {
//         $this->mail->addAttachment($filePath, $fileName);
//     }

   
// }




// public function getDestinatairesend($ID)
// {
//     $sql = "SELECT listclient.Mail
//             FROM listclient
//             INNER JOIN listclientmailing ON listclientmailing.idListClient = listclient.idListClient
//             INNER JOIN mailing ON mailing.IdMailing = listclientmailing.IdMailing
//             INNER JOIN user ON user.IdUser = mailing.IdUser
//             WHERE user.IdUser = ?";

//     return DB::select($sql, [$ID]);
// }

// public function addUser($prenom, $mail, $nom, $userId, $mailingId)
// {
//     DB::beginTransaction();
//     try {
//         $listClientId = DB::table('listclient')->insertGetId([
//             'Mail' => $mail,
//             'Prenom' => $prenom,
//             'Nom' => $nom,
//         ]);

//         if (!DB::table('user')->where('IdUser', $userId)->exists()) {
//             throw new Exception("L'utilisateur avec l'ID $userId n'existe pas.");
//         }

//         if (!DB::table('mailing')->where('IdMailing', $mailingId)->exists()) {
//             throw new Exception("Le mailing avec l'ID $mailingId n'existe pas.");
//         }

//         DB::table('listclientmailing')->insert([
//             'idListClient' => $listClientId,
//             'IdMailing' => $mailingId,
//         ]);

//         DB::commit();
//         return true;
//     } catch (Exception $e) {
//         DB::rollBack();
//         return "Erreur : " . $e->getMessage();
//     }
// }

// public function deleteUser($ID)
// {
//     return DB::table('user')->where('IdUser', $ID)->delete();
// }