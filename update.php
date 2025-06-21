<?php
// Autoriser les requêtes
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Récupérer les données JSON envoyées par JavaScript
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des données
if (
    !$data || 
    !isset($data['index'], $data['titre'], $data['intitule'], $data['categorie'], $data['description'], 
             $data['dateDebut'], $data['dateFin'], $data['duree'], $data['prix'])
) {
    echo json_encode(["success" => false, "message" => "Données incomplètes."]);
    exit;
}

$index = intval($data['index']);
$xmlFile = __DIR__ . '/xml/projet.xml'; // Chemin absolu pour éviter les erreurs

// Vérifier que le fichier existe
if (!file_exists($xmlFile)) {
    echo json_encode(["success" => false, "message" => "Le fichier XML est introuvable."]);
    exit;
}

// Charger le XML
$xml = simplexml_load_file($xmlFile);

// Vérifier l'existence de la formation à l'index donné
if (!isset($xml->formation[$index])) {
    echo json_encode(["success" => false, "message" => "Formation non trouvée à l'index $index."]);
    exit;
}

// Modifier les données XML
$formation = $xml->formation[$index];
$formation->titre = $data['titre'];
$formation->intitule = $data['intitule'];
$formation->categorie = $data['categorie'];
$formation->description = $data['description'];
$formation->dateDebut = $data['dateDebut'];
$formation->dateFin = $data['dateFin'];

// Durée
$duree_parts = explode(" ", trim($data['duree']));
unset($formation->duree);
$duree = $formation->addChild('duree', $duree_parts[0] ?? '');
$duree->addAttribute('unite', $duree_parts[1] ?? 'jours');

// Prix
$prix_parts = explode(" ", trim($data['prix']));
unset($formation->prix);
$prix = $formation->addChild('prix', $prix_parts[0] ?? '');
$prix->addAttribute('devise', $prix_parts[1] ?? 'DA');

// Sauvegarde XML
if (!is_writable($xmlFile)) {
    echo json_encode(["success" => false, "message" => "Le fichier XML n'est pas modifiable. Vérifie les permissions."]);
    exit;
}

$result = $xml->asXML($xmlFile);
if (!$result) {
    echo json_encode(["success" => false, "message" => "Échec de la sauvegarde du fichier XML."]);
    exit;
}

// 🔄 Appel de l'API REST pour modifier dans la base PostgreSQL
$apiUrl = 'http://localhost/api/update.php';

// Ajouter oldTitre (l’ancien titre) pour que l’API REST le trouve dans la base
$data['oldTitre'] = $formation->titre->__toString(); // Avant modification !

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // <- maintenant on utilise PUT
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($data))
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Données en JSON

$apiResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Vérification de l'appel API
if ($apiResponse === false) {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'appel API PostgreSQL."]);
    exit;
}

// ✅ Réponse finale
echo json_encode([
    "success" => true,
    "message" => "Formation mise à jour dans le XML et base de données.",
    "api_status_code" => $httpCode,
    "api_response" => json_decode($apiResponse, true)
]);