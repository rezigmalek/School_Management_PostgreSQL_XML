<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catalogue des Formations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
// Charger le fichier XML
$xml = new DOMDocument;
$xml->load('xml/projet.xml');

// Charger le fichier XSL
$xsl = new DOMDocument;
$xsl->load('xml/transform.xsl');

// Initialiser le processeur XSLT
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl);

// Afficher le résultat de la transformation
echo $proc->transformToXML($xml);
?>

<br>
<div class="text-center">
    <a href="ajout.php">
        <button type="button">Ajouter une formation</button>
    </a>
</div>
<br>

<script>
document.getElementById('filterForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseFloat(document.getElementById('maxPrice').value) || 1000000;
    const minDuration = parseFloat(document.getElementById('minDuration').value) || 0;
    const maxDuration = parseFloat(document.getElementById('maxDuration').value) || 100000;

    const response = await fetch('xml/projet.xml');
    const xmlText = await response.text();
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlText, 'text/xml');

    // XPath seulement pour le prix
    const xpath = `/catalogue/formation[number(prix) >= ${minPrice} and number(prix) <= ${maxPrice}]`;
    const results = xmlDoc.evaluate(xpath, xmlDoc, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

    const tbody = document.getElementById('resultBody');
    tbody.innerHTML = '';

    for (let i = 0; i < results.snapshotLength; i++) {
        const formation = results.snapshotItem(i);

        const titre = formation.getElementsByTagName("titre")[0].textContent;
        const prix = formation.getElementsByTagName("prix")[0].textContent;
        const dureeEl = formation.getElementsByTagName("duree")[0];
        const unite = dureeEl.getAttribute("unite");
        const dureeVal = parseInt(dureeEl.textContent);

        let dureeJours = dureeVal;
        if (unite === "semaines") dureeJours *= 7;
        else if (unite === "mois") dureeJours *= 30;

        if (dureeJours >= minDuration && dureeJours <= maxDuration) {
            const row = `<tr>
                <td>${titre}</td>
                <td>${dureeJours}</td>
                <td>${prix}</td>
            </tr>`;
            tbody.innerHTML += row;
        }
    }
});
</script>

<!-- Scripts Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<style>
form {
    margin-bottom: 20px;
}
button[type="button"] {
    background-color: #007bff; /* bleu Bootstrap */
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

button[type="button"]:hover {
    background-color: #0056b3;
}
</style>

</body>
</html>
