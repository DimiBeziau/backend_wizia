<?php

require __DIR__.'/../vendor/autoload.php';  // Inclure l'autoloader de Composer
$app = require_once __DIR__.'/../bootstrap/app.php';  // Charger l'application Laravel

use App\Http\Controllers\C_MailController;

$mailer = new C_MailController;

// Définir les infos de l'email
$to = 'querelmatthieu@gmail.com';
$subject = 'Test de l\'envoi d\'email';
$body = 'Ceci est un test d\'envoi d\'email avec <b>PHPMailer</b> en HTML.';
$altBody = 'Ceci est un test d\'envoi d\'email avec PHPMailer en texte brut.';

// Envoyer
// if ($mailer->generateMail($to, $subject, $body, $altBody)) {
//     echo "Email envoyé avec succès!";
// } else {
//     echo "L'envoi de l'email a échoué.";
// }
