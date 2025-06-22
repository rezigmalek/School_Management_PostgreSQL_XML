<?php
include_once 'includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $titre = $_POST['titre'];
    $intitule = $_POST['intitule'];
    $categorie = $_POST['categorie'];
    $description = $_POST['description'];
    $formateur = $_POST['formateur'];
    $duree = $_POST['duree'];
    $duree_unite = $_POST['duree_unite'];
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    $type = $_POST['type'];
    $prix = $_POST['prix'];
    $devise = $_POST['devise'];

    
    $xml = simplexml_load_file('xml/projet.xml');

   
    $formation = $xml->addChild('formation');
    $formation->addChild('titre', $titre);
    $formation->addChild('intitule', $intitule);
    $formation->addChild('categorie', $categorie);
    $formation->addChild('description', $description);
    $formation->addChild('formateur', $formateur);
    $dureeElement = $formation->addChild('duree', $duree);
    $dureeElement->addAttribute('unite', $duree_unite);
    $formation->addChild('dateDebut', $dateDebut);
    $formation->addChild('dateFin', $dateFin);
    $formation->addChild('type', $type);
    $priceElement = $formation->addChild('prix', $prix);
    $priceElement->addAttribute('devise', $devise);

    
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());

    
    if ($dom->save('xml/projet.xml')) {
        
        $domFinal = new DOMDocument();
        $domFinal->load('xml/projet.xml');
        $formations = $domFinal->getElementsByTagName('formation');

        foreach ($formations as $f) {
            $singleDom = new DOMDocument('1.0', 'UTF-8');
            $imported = $singleDom->importNode($f, true);
            $singleDom->appendChild($imported);

            $xmlContent = $singleDom->saveXML();

            
            $check = $pdo->prepare("SELECT COUNT(*) FROM formations WHERE contenu_xml::text = :xml");
            $check->bindParam(':xml', $xmlContent);
            $check->execute();
            $count = $check->fetchColumn();

            if ($count == 0) {
                $insert = $pdo->prepare("INSERT INTO formations (contenu_xml) VALUES (:xml)");
                $insert->bindParam(':xml', $xmlContent);
                $insert->execute();
            }
        }

        
        header("Location: api/index.php?message=Formation ajoutée avec succès");
        exit();
    } else {
        header("Location: api/index.php?message=Erreur lors de l'ajout de la formation");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Formation</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <hr>
        <h2 class="text-center">Ajouter une nouvelle formation</h2>
        <hr>
        <form method="POST" action="ajout.php">
            <div class="form-group"><h5>Titre:</h5><input type="text" class="form-control" name="titre" required></div>
            <div class="form-group"><h5>Intitulé:</h5><input type="text" class="form-control" name="intitule" required></div>
            <div class="form-group"><h5>Catégorie:</h5><input type="text" class="form-control" name="categorie" required></div>
            <div class="form-group"><h5>Description:</h5><textarea class="form-control" name="description" rows="4" required></textarea></div>
            <div class="form-group"><h5>Formateur:</h5><input type="text" class="form-control" name="formateur" required></div>
            <div class="form-group"><h5>Durée:</h5><input type="number" class="form-control" name="duree" required></div>
            <div class="form-group"><h5>Unité de durée:</h5><input type="text" class="form-control" name="duree_unite" required></div>
            <div class="form-group"><h5>Date de début:</h5><input type="date" class="form-control" name="date_debut" required></div>
            <div class="form-group"><h5>Date de fin:</h5><input type="date" class="form-control" name="date_fin" required></div>
            <div class="form-group"><h5>Type:</h5><input type="text" class="form-control" name="type" required></div>
            <div class="form-group"><h5>Prix:</h5><input type="number" class="form-control" name="prix" required></div>
            <div class="form-group"><h5>Devise:</h5><input type="text" class="form-control" name="devise" required></div>
            <button type="submit" class="btn btn-primary btn-block">Ajouter la formation</button>
        </form>
    </div>
    <br>
    <br>
</body>
</html>
