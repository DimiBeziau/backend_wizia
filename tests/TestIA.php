<?php

require_once __DIR__ . '/../vendor/autoload.php'; 

use App\Http\Controllers\C_IAController;

// Instancier ton contrôleur
$generation = new C_IAController();

// Appeler ta méthode avec un prompt
$response = $generation->generatprompt("salut mon ia préférée");

// Afficher la réponse
echo $response;
