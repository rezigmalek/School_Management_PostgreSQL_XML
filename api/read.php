<?php
// Assure-toi qu'il n'y a rien avant cette ligne pour éviter les erreurs de contenu
header("Content-Type: application/xml; charset=UTF-8");

// Inclure la base de données
include_once '../includes/db.php';

try {
    // Exécuter la requête pour récupérer le contenu XML des formations
    $stmt = $pdo->query("SELECT contenu_xml FROM formations");

    // Créer un document XML global
    $xml = new DOMDocument('1.0', 'UTF-8');
    $root = $xml->createElement("formations");

    // Boucle sur chaque formation dans la base de données
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $contenuXML = $row['contenu_xml'];

        // Charger le contenu XML de la formation
        $formationDoc = new DOMDocument();
        $formationDoc->loadXML($contenuXML);

        // Importer le noeud <formation> du document individuel dans le document principal
        $formationNode = $xml->importNode($formationDoc->documentElement, true);
        $root->appendChild($formationNode);
    }

    // Ajouter la racine au document XML
    $xml->appendChild($root);

    // Afficher le contenu XML
    echo $xml->saveXML();
} catch (PDOException $e) {
    // En cas d'erreur, retourner un message d'erreur XML
    echo "<error>Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</error>";
}
?>
