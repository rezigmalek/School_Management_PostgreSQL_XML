<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <title>Liste des Formations</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f9f9f9; }
    h2 { text-align: center; margin-top: 20px; }
    table { width: 95%; margin: auto; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background-color: #007BFF; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    #editSection { display: none; margin-top: 20px; width: 80%; margin: auto; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="text-center mb-4">Liste des Formations</h2>

    <!-- Filtres -->
    <div class="row mb-4">
      <div class="col-md-3">
        <label for="categorieFilter">Catégorie :</label>
        <select id="categorieFilter" class="form-control">
          <option value="">-- Toutes --</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="minDuree">Durée min :</label>
        <input type="number" id="minDuree" class="form-control" />
      </div>
      <div class="col-md-3">
        <label for="maxDuree">Durée max :</label>
        <input type="number" id="maxDuree" class="form-control" />
      </div>
      <div class="col-md-3">
        <label for="minPrix">Prix min :</label>
        <input type="number" id="minPrix" class="form-control" />
      </div>
      <div class="col-md-3 mt-2">
        <label for="maxPrix">Prix max :</label>
        <input type="number" id="maxPrix" class="form-control" />
      </div>
      <div class="col-md-3 mt-4">
        <button class="btn btn-primary mt-2" onclick="afficherFormations()">Filtrer</button>
      </div>
    </div>

    <!-- Tableau -->
    <table class="table table-bordered" id="formationsTable">
      <thead>
        <tr>
          <th>Titre</th>
          <th>Intitulé</th>
          <th>Catégorie</th>
          <th>Description</th>
          <th>Date début</th>
          <th>Date fin</th>
          <th>Formateur</th>
          <th>Durée</th>
          <th>Prix</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Formulaire d'édition -->
  <div id="editSection">
    <h3>Modifier la formation</h3>
    <form id="editForm">
      <input type="hidden" id="editId" />
      <div class="form-group"><label>Titre :</label><input type="text" class="form-control" id="editTitre"></div>
      <div class="form-group"><label>Intitulé :</label><input type="text" class="form-control" id="editIntitule"></div>
      <div class="form-group"><label>Catégorie :</label><input type="text" class="form-control" id="editCategorie"></div>
      <div class="form-group"><label>Description :</label><input type="text" class="form-control" id="editDescription"></div>
      <div class="form-group"><label>Date début :</label><input type="text" class="form-control" id="editDateDebut"></div>
      <div class="form-group"><label>Date fin :</label><input type="text" class="form-control" id="editDateFin"></div>
      <div class="form-group"><label>Formateur :</label><input type="text" class="form-control" id="editFormateur"></div>
      <div class="form-group"><label>Durée :</label><input type="text" class="form-control" id="editDuree"></div>
      <div class="form-group"><label>Prix :</label><input type="text" class="form-control" id="editPrix"></div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
    <div id="editMessage" class="mt-3"></div>
  </div>

  <script>
    let xmlDataGlobal = null;

    async function fetchFormations() {
      const res = await fetch('http://localhost/cawa_projet_postgres/api/read.php');
      const xmlStr = await res.text();
      xmlDataGlobal = new DOMParser().parseFromString(xmlStr, "application/xml");
      remplirFiltreCategories(xmlDataGlobal);
      afficherFormations();
    }

    function afficherFormations() {
      const tbody = document.querySelector("#formationsTable tbody");
      tbody.innerHTML = "";

      const selectedCat = document.getElementById("categorieFilter").value;
      const minDuree = parseFloat(document.getElementById("minDuree").value) || 0;
      const maxDuree = parseFloat(document.getElementById("maxDuree").value) || Infinity;
      const minPrix = parseFloat(document.getElementById("minPrix").value) || 0;
      const maxPrix = parseFloat(document.getElementById("maxPrix").value) || Infinity;

      let xpathQuery = selectedCat 
        ? `//formation[categorie[text()="${selectedCat}"]]` 
        : "//formation";

      const formations = xmlDataGlobal.evaluate(
        xpathQuery,
        xmlDataGlobal,
        null,
        XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
        null
      );

      for (let i = 0; i < formations.snapshotLength; i++) {
    const formation = formations.snapshotItem(i);

    const dureeVal = parseFloat(formation.getElementsByTagName("duree")[0]?.textContent) || 0;
    const prixVal = parseFloat(formation.getElementsByTagName("prix")[0]?.textContent) || 0;
    const dureeUnite = formation.getElementsByTagName("duree")[0]?.getAttribute("unite") || "";

    // Conversion de la durée pour filtrage
    let dureePourFiltrage = dureeVal;

    // Conversion de l'entrée de l'utilisateur en semaines
    const minDureeConvertie = convertToWeeks(minDuree, "jours");
    const maxDureeConvertie = convertToWeeks(maxDuree, "jours");

    // Convertir les unités selon le cas
    if (dureeUnite === "jours") {
        dureePourFiltrage = dureeVal / 7; // Convertir jours en semaines
    } else if (dureeUnite === "mois") {
        dureePourFiltrage = dureeVal * 4; // Convertir mois en semaines (approximatif)
    }

    // Filtrage selon la durée et le prix
    if (dureePourFiltrage < minDureeConvertie || dureePourFiltrage > maxDureeConvertie || prixVal < minPrix || prixVal > maxPrix) {
        continue;
    }

    const titre = formation.getElementsByTagName("titre")[0]?.textContent ?? "";
    const intitule = formation.getElementsByTagName("intitule")[0]?.textContent ?? "";
    const categorie = formation.getElementsByTagName("categorie")[0]?.textContent ?? "";
    const description = formation.getElementsByTagName("description")[0]?.textContent ?? "";
    const dateDebut = formation.getElementsByTagName("dateDebut")[0]?.textContent ?? "";
    const dateFin = formation.getElementsByTagName("dateFin")[0]?.textContent ?? "";
    const formateur = formation.getElementsByTagName("formateur")[0]?.textContent ?? "";
    const duree = formation.getElementsByTagName("duree")[0]?.textContent + " " + (formation.getElementsByTagName("duree")[0]?.getAttribute("unite") || "");
    const prix = formation.getElementsByTagName("prix")[0]?.textContent + " " + (formation.getElementsByTagName("prix")[0]?.getAttribute("devise") || "");

    const row = tbody.insertRow();
    row.innerHTML = `
      <td>${titre}</td>
      <td>${intitule}</td>
      <td>${categorie}</td>
      <td>${description}</td>
      <td>${dateDebut}</td>
      <td>${dateFin}</td>
      <td>${formateur}</td>
      <td>${duree}</td>
      <td>${prix}</td>
      <td>
        <button class="btn btn-warning btn-sm" onclick="remplirFormulaire('${titre}', '${intitule}', '${categorie}', '${description}', '${dateDebut}', '${dateFin}', '${formateur}', '${duree}', '${prix}')">Modifier</button>
        <button class="btn btn-danger btn-sm ml-2" onclick="deleteFormation('${titre}')">Supprimer</button>
      </td>`;
}

// Fonction pour convertir les durées entrées en semaines
function convertToWeeks(duree, unite) {
    if (unite === "jours") {
        return duree / 7; // Conversion des jours en semaines
    } else if (unite === "mois") {
        return duree * 4; // Conversion des mois en semaines (approximatif)
    } else {
        return duree; // Si c'est déjà en semaines
    }
}


    }

    function remplirFiltreCategories(xmlDoc) {
      const select = document.getElementById("categorieFilter");
      const categories = new Set();
      const formations = xmlDoc.getElementsByTagName("formation");

      for (let f of formations) {
        const cat = f.getElementsByTagName("categorie")[0]?.textContent;
        if (cat) categories.add(cat);
      }

      categories.forEach(cat => {
        const opt = document.createElement("option");
        opt.value = opt.textContent = cat;
        select.appendChild(opt);
      });
    }

    function remplirFormulaire(titre, intitule, categorie, description, dateDebut, dateFin, formateur, duree, prix) {
      document.getElementById("editId").value = titre;
      document.getElementById("editTitre").value = titre;
      document.getElementById("editIntitule").value = intitule;
      document.getElementById("editCategorie").value = categorie;
      document.getElementById("editDescription").value = description;
      document.getElementById("editDateDebut").value = dateDebut;
      document.getElementById("editDateFin").value = dateFin;
      document.getElementById("editFormateur").value = formateur;
      document.getElementById("editDuree").value = duree.split(" ")[0];
      document.getElementById("editPrix").value = prix.split(" ")[0];
      document.getElementById("editSection").style.display = "block";
      window.scrollTo({ top: document.getElementById("editSection").offsetTop, behavior: "smooth" });
    }

    document.getElementById("editForm").addEventListener("submit", function (e) {
      e.preventDefault();
      const data = {
        oldTitre: document.getElementById("editId").value,
        titre: document.getElementById("editTitre").value,
        intitule: document.getElementById("editIntitule").value,
        categorie: document.getElementById("editCategorie").value,
        description: document.getElementById("editDescription").value,
        dateDebut: document.getElementById("editDateDebut").value,
        dateFin: document.getElementById("editDateFin").value,
        formateur: document.getElementById("editFormateur").value,
        duree: document.getElementById("editDuree").value,
        prix: document.getElementById("editPrix").value,
      };

      fetch("http://localhost/cawa_projet_postgres/api/update.php", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(res => {
        document.getElementById("editMessage").innerHTML = `<div class="alert alert-success">${res.message}</div>`;
        setTimeout(() => location.reload(), 1000);
      })
      .catch(() => {
        document.getElementById("editMessage").innerHTML = `<div class="alert alert-danger">Erreur de mise à jour</div>`;
      });
    });

    function deleteFormation(titre) {
      if (!confirm(`Confirmer la suppression de "${titre}" ?`)) return;

      fetch("http://localhost/cawa_projet_postgres/api/delete.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ titre })
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message || "Formation supprimée.");
        location.reload();
      })
      .catch(() => alert("Erreur de suppression"));
    }

    fetchFormations();
  </script>
</body>
</html>
