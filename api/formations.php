<?php
header("Content-Type: application/xml; charset=UTF-8");
require_once("../includes/db.php");

try {
    $stmt = $pdo->query("SELECT * FROM formations");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><formations></formations>');

    foreach ($formations as $formation) {
        $f = $xml->addChild('formation');
        $f->addChild('id', $formation['id']);
        $f->addChild('titre', htmlspecialchars($formation['titre']));
        $f->addChild('description', htmlspecialchars($formation['description']));
        $f->addChild('duree', $formation['duree']);
    }

    ob_clean(); // important
    echo $xml->asXML();

} catch (PDOException $e) {
    http_response_code(500);
    echo "<error>Erreur : " . $e->getMessage() . "</error>";
}
