<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['oldTitre'])) {
    http_response_code(400);
    echo json_encode(["error" => "Paramètre requis manquant : oldTitre"]);
    exit();
}

$oldTitre = $data['oldTitre'];
$newTitre = $data['titre'] ?? '';
$newIntitule = $data['intitule'] ?? '';
$newCategorie = $data['categorie'] ?? '';
$newDescription = $data['description'] ?? '';
$newDateDebut = $data['dateDebut'] ?? '';
$newDateFin = $data['dateFin'] ?? '';
$newFormateur = $data['formateur'] ?? '';
$newDuree = $data['duree'] ?? '';
$newPrix = $data['prix'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, contenu_xml FROM formations WHERE contenu_xml::text LIKE '%' || :oldTitre || '%'");
    $stmt->bindParam(':oldTitre', $oldTitre);
    $stmt->execute();
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated = 0;

    foreach ($formations as $formation) {
        $xmlText = $formation['contenu_xml'];
        $id = $formation['id'];

        // Mettre à jour les balises avec ou sans attributs
        if (!empty($newTitre)) {
            $xmlText = preg_replace('/<titre>.*?<\/titre>/i', "<titre>" . htmlspecialchars($newTitre) . "</titre>", $xmlText);
        }
        if (!empty($newIntitule)) {
            $xmlText = preg_replace('/<intitule>.*?<\/intitule>/i', "<intitule>" . htmlspecialchars($newIntitule) . "</intitule>", $xmlText);
        }
        if (!empty($newCategorie)) {
            $xmlText = preg_replace('/<categorie>.*?<\/categorie>/i', "<categorie>" . htmlspecialchars($newCategorie) . "</categorie>", $xmlText);
        }
        if (!empty($newDescription)) {
            $xmlText = preg_replace('/<description>.*?<\/description>/i', "<description>" . htmlspecialchars($newDescription) . "</description>", $xmlText);
        }
        if (!empty($newDateDebut)) {
            $xmlText = preg_replace('/<dateDebut>.*?<\/dateDebut>/i', "<dateDebut>" . htmlspecialchars($newDateDebut) . "</dateDebut>", $xmlText);
        }
        if (!empty($newDateFin)) {
            $xmlText = preg_replace('/<dateFin>.*?<\/dateFin>/i', "<dateFin>" . htmlspecialchars($newDateFin) . "</dateFin>", $xmlText);
        }
        if (!empty($newFormateur)) {
            $xmlText = preg_replace('/<formateur>.*?<\/formateur>/i', "<formateur>" . htmlspecialchars($newFormateur) . "</formateur>", $xmlText);
        }
        if (!empty($newDuree)) {
            $xmlText = preg_replace('/<duree.*?>.*?<\/duree>/i', '<duree unite="jours">' . htmlspecialchars($newDuree) . '</duree>', $xmlText);
        }
        if (!empty($newPrix)) {
            $xmlText = preg_replace('/<prix.*?>.*?<\/prix>/i', '<prix devise="DZD">' . htmlspecialchars($newPrix) . '</prix>', $xmlText);
        }

        // Mise à jour dans la base avec conversion XML
        $update = $pdo->prepare("UPDATE formations SET contenu_xml = XMLPARSE(DOCUMENT :xmlText) WHERE id = :id");
        $update->bindParam(':xmlText', $xmlText);
        $update->bindParam(':id', $id, PDO::PARAM_INT);
        $update->execute();

        if ($update->rowCount() > 0) {
            $updated++;
        }
    }

    if ($updated > 0) {
        echo json_encode(["message" => "Mise à jour effectuée sur $updated formation(s)."]);
    } else {
        echo json_encode(["message" => "Aucune mise à jour effectuée."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur SQL : " . $e->getMessage()]);
}
