<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    exclude-result-prefixes="xs"
    version="1.0">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html>
            <head>
                <title>Catalogue des Formations</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f9f9f9;
                    }
                    h1 {
                        text-align: center;
                    }
                    table {
                        width: 95%;
                        margin: auto;
                        border-collapse: collapse;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    }
                    th, td {
                        padding: 10px;
                        border: 1px solid #ccc;
                        text-align: left;
                    }
                    th {
                        background-color: #007BFF;
                        color: white;
                    }
                    tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    #editForm {
                        width: 60%;
                        margin: 20px auto;
                        padding: 20px;
                        background-color: #fff;
                        border: 1px solid #ccc;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        display: none;
                    }
                    #editForm input {
                        margin-bottom: 10px;
                        width: 100%;
                        padding: 8px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                    }
                    #editForm button {
                        padding: 10px 20px;
                        background-color: #28a745;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                    }
                    button.edit-btn {
                        background-color: #ffc107; /* jaune */
                        color: black;
                        border: none;
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-weight: bold;
                        cursor: pointer;
                        transition: background-color 0.3s;
                    }

                    button.edit-btn:hover {
                        background-color: #e0a800;
                    }

                    button.delete-btn {
                        background-color: #dc3545; /* rouge */
                        color: white;
                        border: none;
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-weight: bold;
                        cursor: pointer;
                        transition: background-color 0.3s;
                    }

                    button.delete-btn:hover {
                        background-color: #c82333;
                    }
                </style>
            </head>
            <body>
                <h1>Catalogue des Formations</h1>
                <table>
                    <tr>
                        <th>Titre</th>
                        <th>Intitulé</th>
                        <th>Catégorie</th>
                        <th>Description</th>
                        <th>Durée</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Prix</th>
                        <th>Modification</th>
                        <th>Supression</th>
                    </tr>
                    <xsl:for-each select="centreFormations/formation">
                        <tr data-index="{position() - 1}">
                            <td><xsl:value-of select="titre"/></td>
                            <td><xsl:value-of select="intitule"/></td>
                            <td><xsl:value-of select="categorie"/></td>
                            <td><xsl:value-of select="description"/></td>
                            <td>
                                <xsl:value-of select="duree"/>
                                <xsl:text> </xsl:text>
                                <xsl:value-of select="duree/@unite"/>
                            </td>
                            <td><xsl:value-of select="dateDebut"/></td>
                            <td><xsl:value-of select="dateFin"/></td>
                            <td>
                                <xsl:value-of select="prix"/>
                                <xsl:text> </xsl:text>
                                <xsl:value-of select="prix/@devise"/>
                            </td>
                            <td>
                                <button class="edit-btn">Modifier</button>
                            </td>
                            <td>
                                <button class="delete-btn">Supprimer</button>
                            </td>
                        </tr>
                    </xsl:for-each>
                </table>

                <!-- Formulaire de modification -->
                <form id="editForm">
                    <h3>Modifier une formation</h3>

                    <input type="text" id="editTitre" placeholder="Titre"/>
                    <input type="text" id="editIntitule" placeholder="Intitulé"/>
                    <input type="text" id="editCategorie" placeholder="Catégorie"/>
                    <input type="text" id="editDescription" placeholder="Description"/>
                    <input type="text" id="editDuree" placeholder="Durée (ex: 3 jours)"/>
                    <input type="text" id="editDateDebut" placeholder="Date début"/>
                    <input type="text" id="editDateFin" placeholder="Date fin"/>
                    <input type="text" id="editPrix" placeholder="Prix (ex: 2500 DA)"/>

                    <button type="submit">Mettre à jour</button>
                </form>

                <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        const editForm = document.getElementById("editForm");
        const inputs = {
            titre: document.getElementById("editTitre"),
            intitule: document.getElementById("editIntitule"),
            categorie: document.getElementById("editCategorie"),
            description: document.getElementById("editDescription"),
            duree: document.getElementById("editDuree"),
            dateDebut: document.getElementById("editDateDebut"),
            dateFin: document.getElementById("editDateFin"),
            prix: document.getElementById("editPrix")
        };
        let currentRow = null;

        // Bouton supprimer (optionnel, pas encore connecté au backend)
        document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function () {
        const row = this.closest("tr");
        const index = row.getAttribute("data-index");

        // Appel AJAX pour supprimer dans le fichier XML
        fetch("delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ index: index })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === "success") {
                row.remove(); // Supprimer visuellement dans la table
            } else {
                alert("success : la suppression est terminée");
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression :", error);
        });
    });
});
document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function () {
                const row = this.closest("tr");
                // Ici tu peux ajouter un fetch pour delete.php si besoin
                row.remove();
            });
        });

        // Bouton modifier
        document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function () {
                currentRow = this.closest("tr");
                const cells = currentRow.querySelectorAll("td");

                inputs.titre.value = cells[0].textContent.trim();
                inputs.intitule.value = cells[1].textContent.trim();
                inputs.categorie.value = cells[2].textContent.trim();
                inputs.description.value = cells[3].textContent.trim();
                inputs.duree.value = cells[4].textContent.trim();
                inputs.dateDebut.value = cells[5].textContent.trim();
                inputs.dateFin.value = cells[6].textContent.trim();
                inputs.prix.value = cells[7].textContent.trim();

                editForm.style.display = "block";
                window.scrollTo({ top: editForm.offsetTop, behavior: 'smooth' });
            });
        });

        // Soumission du formulaire de modification
        editForm.addEventListener("submit", function (e) {
            e.preventDefault();
            if (currentRow) {
                const index = currentRow.getAttribute("data-index"); // position de la ligne

                const updatedData = {
                    titre: inputs.titre.value,
                    intitule: inputs.intitule.value,
                    categorie: inputs.categorie.value,
                    description: inputs.description.value,
                    duree: inputs.duree.value,
                    dateDebut: inputs.dateDebut.value,
                    dateFin: inputs.dateFin.value,
                    prix: inputs.prix.value,
                    index: index
                };

                // Appel AJAX pour sauvegarder dans le fichier XML via PHP
                fetch("update.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(updatedData)
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // message de succès
                    // Mettre à jour la ligne du tableau HTML
                    const cells = currentRow.querySelectorAll("td");
                    cells[0].textContent = updatedData.titre;
                    cells[1].textContent = updatedData.intitule;
                    cells[2].textContent = updatedData.categorie;
                    cells[3].textContent = updatedData.description;
                    cells[4].textContent = updatedData.duree;
                    cells[5].textContent = updatedData.dateDebut;
                    cells[6].textContent = updatedData.dateFin;
                    cells[7].textContent = updatedData.prix;

                    editForm.style.display = "none";
                    currentRow = null;
                })
                .catch(error => {
                    console.error("Erreur lors de la mise à jour :", error);
                });
            }
        });
    });
</script>

            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
