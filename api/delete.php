<?php
// Autoriser les requêtes cross-origin (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../includes/db.php'; // Connexion à la base de données

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents("php://input"));

// Vérifier que le titre est présent dans les données reçues
if (!isset($data->titre)) {
    http_response_code(400);
    echo json_encode(["message" => "Le champ 'titre' est requis."]);
    exit;
}

$titre = htmlspecialchars($data->titre);

// Requête SQL pour supprimer la formation basée sur le titre dans le contenu XML
try {
    // Utilisation de array_length() pour vérifier qu'il y a bien un titre correspondant
    $sql = "DELETE FROM formations
            WHERE array_length(xpath('//titre[text() = \"$titre\"]', contenu_xml::xml), 1) > 0";

    // Préparer la requête SQL
    $stmt = $pdo->prepare($sql);

    // Exécuter la requête
    $stmt->execute();

    // Vérifier si des lignes ont été supprimées
    if ($stmt->rowCount() > 0) {
        echo json_encode(["message" => "Formation supprimée avec succès."]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Aucune formation trouvée avec ce titre."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la suppression : " . $e->getMessage()]);
}
?>
