<?php

include_once 'includes/db.php';

$dom = new DOMDocument();
$dom->load('xml/projet.xml');

// Récupérer toutes les balises <formation>
$formations = $dom->getElementsByTagName('formation');

foreach ($formations as $formation) {
    // Importer la balise <formation> seule dans un nouveau DOM
    $singleDom = new DOMDocument('1.0', 'UTF-8');
    $singleDom->formatOutput = false; // Pour éviter des différences d'espaces ou sauts de ligne
    $imported = $singleDom->importNode($formation, true);
    $singleDom->appendChild($imported);

    // Convertir en chaîne XML
    $xmlContent = $singleDom->saveXML();

    // Vérifier si ce contenu existe déjà (exact match du XML)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM formations WHERE contenu_xml::text = :xml");
    $checkStmt->bindParam(':xml', $xmlContent);
    $checkStmt->execute();
    $exists = $checkStmt->fetchColumn();

    if ($exists == 0) {
        // Insérer uniquement si pas déjà présent
        $stmt = $pdo->prepare("INSERT INTO formations (contenu_xml) VALUES (:xml)");
        $stmt->bindParam(':xml', $xmlContent);
        $stmt->execute();
        echo "✅ Formation ajoutée\n";
    } else {
        echo "⚠️ Formation déjà existante, ignorée\n";
    }
}

echo "✅ Importation terminée sans doublons.\n";

?>
