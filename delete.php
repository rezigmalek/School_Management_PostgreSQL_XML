<?php
// En-têtes pour CORS et JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Inclure la connexion DB
include_once 'includes/db.php';

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"), true);

// Vérifier la présence de l'index
if (!isset($data['index'])) {
    echo json_encode(["success" => false, "message" => "Index de la formation non fourni."]);
    exit;
}

$index = intval($data['index']);
$xmlFile = 'xml/projet.xml';

if (!file_exists($xmlFile)) {
    echo json_encode(["success" => false, "message" => "Le fichier XML est introuvable."]);
    exit;
}

// Charger le XML
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->load($xmlFile);

$formations = $dom->getElementsByTagName("formation");

if ($index < 0 || $index >= $formations->length) {
    echo json_encode(["success" => false, "message" => "La formation sélectionnée n'existe pas."]);
    exit;
}

// Récupérer le titre avant suppression (pour l’utiliser dans la requête SQL)
$titreNode = $formations->item($index)->getElementsByTagName("titre")->item(0);
$titre = $titreNode ? $titreNode->nodeValue : null;

if (!$titre) {
    echo json_encode(["success" => false, "message" => "Impossible de récupérer le titre."]);
    exit;
}

// Supprimer l'élément du XML
$formationToRemove = $formations->item($index);
$formationToRemove->parentNode->removeChild($formationToRemove);

// Sauvegarder le fichier XML
$dom->save($xmlFile);

// 🔁 Supprimer aussi depuis PostgreSQL en se basant sur le titre
try {
    $titre = htmlspecialchars($titre);
    $sql = "DELETE FROM formations WHERE array_length(xpath('//titre[text() = \"$titre\"]', contenu_xml::xml), 1) > 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "La formation a été supprimée du XML et de la base de données."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur DB : " . $e->getMessage()]);
}
