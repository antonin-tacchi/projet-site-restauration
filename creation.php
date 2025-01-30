<?php
$pdo = new PDO('mysql:host=localhost;dbname=site-restauration', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




// creation de categorie
if (isset($_POST['envoi'])) {
    if (isset($_POST['nom_categorie']) and !empty($_POST['nom_categorie'])) {
        $nom_categorie = htmlspecialchars($_POST['nom_categorie']);
        $recup_categorie = $pdo->prepare('SELECT * FROM categorie WHERE nom_categorie = ?');
        $recup_categorie->execute(array($nom_categorie));

        if ($recup_categorie->rowCount() > 0) {

        } else {
            $insert_categorie = $pdo->prepare('INSERT INTO `categorie` (`nom_categorie`) VALUES (?)');
            $insert_categorie->execute(array($nom_categorie));

        }
    }
}
// suppression de categorie
if (isset($_POST['supprimer'])) {
    if (isset($_POST['nom_categorie']) and !empty($_POST['nom_categorie'])) {
        $nom_categorie = htmlspecialchars($_POST['nom_categorie']);
        $recup_categorie = $pdo->prepare('SELECT * FROM categorie WHERE nom_categorie = ?');
        $recup_categorie->execute(array($nom_categorie));

        if ($recup_categorie->rowCount() > 0) {
            $delete_categorie = $pdo->prepare('DELETE FROM categorie WHERE nom_categorie = ?');
            $delete_categorie->execute(array($nom_categorie));

        }
    }
}




// creation d'ingredient
if (isset($_POST['envoie'])) {
    if (isset($_POST['nom_ingredient']) and !empty($_POST['nom_ingredient'])) {
        $nom_ingredient = htmlspecialchars($_POST['nom_ingredient']);
        $recupingredient = $pdo->prepare('SELECT * FROM ingredient WHERE nom_ingredient = ?');
        $recupingredient->execute(array($nom_ingredient));

        if ($recupingredient->rowCount() > 0) {

        } else {
            $insert_ingredient = $pdo->prepare('INSERT INTO `ingredient` (`nom_ingredient`) VALUES (?)');
            $insert_ingredient->execute(array($nom_ingredient));
        }
    }
}
// suppresion d'ingredient
if (isset($_POST['supprimé'])) {
    if (isset($_POST['nom_ingredient']) and !empty($_POST['nom_ingredient'])) {
        $nom_ingredient = htmlspecialchars($_POST['nom_ingredient']);
        $recupingredient = $pdo->prepare('SELECT * FROM ingredient WHERE nom_ingredient = ?');
        $recupingredient->execute(array($nom_ingredient));

        if ($recupingredient->rowCount() > 0) {
            $deleteingredient = $pdo->prepare('DELETE FROM ingredient WHERE nom_ingredient = ?');
            $deleteingredient->execute(array($nom_ingredient));
        }
    }
}




// Ajouter un plat (si formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_plat'])) {
    $nom_plat = $_POST['nom_plat'];
    $prix_plat = $_POST['prix_plat'];
    $description_plat = $_POST['description_plat']; // Description du plat
    $nom_categorie = $_POST['nom_categorie']; // Nom de la catégorie sélectionnée
    $image_plat = $_POST['image_plat']; // Récupération de l'URL de l'image

    // Insérer le plat dans la table 'plat' avec l'URL de l'image
    $stmt = $pdo->prepare("INSERT INTO plat (nom_plat, prix, description_plat, image_plat) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom_plat, $prix_plat, $description_plat, $image_plat]);

    // Récupérer l'ID du plat inséré
    $plat_id = $pdo->lastInsertId();

    // Récupérer l'ID de la catégorie sélectionnée
    $stmt_categorie = $pdo->prepare("SELECT id FROM categorie WHERE nom_categorie = ?");
    $stmt_categorie->execute([$nom_categorie]);
    $categorie = $stmt_categorie->fetch(PDO::FETCH_ASSOC);

    if ($categorie) {
        // Si la catégorie existe, associer le plat à cette catégorie
        $categorie_id = $categorie['id'];
// Insérer dans la table de liaison categorie_plat
        $stmt_associer = $pdo->prepare("INSERT INTO categorie_plat (id_plat, id_categorie) VALUES (?, ?)");
        $stmt_associer->execute([$plat_id, $categorie_id]);
    } else {
        // Si la catégorie n'existe pas, afficher un message
        echo "La catégorie spécifiée n'existe pas.";
    }

    // Rediriger après l'ajout du plat pour éviter un rechargement de la page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Récupérer tous les plats avec leurs catégories associées
$query_plats = $pdo->query("SELECT plat.*, GROUP_CONCAT(categorie.nom_categorie) AS categories
                             FROM plat
                             LEFT JOIN categorie_plat ON plat.id = categorie_plat.id_plat
                             LEFT JOIN categorie ON categorie_plat.id_categorie = categorie.id
                             GROUP BY plat.id");
$plats = $query_plats->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories
$query_categories = $pdo->query("SELECT nom_categorie FROM categorie");
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);

    
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Création</title>
</head>
<header>
    <a href="plat.php"><h2 class="text-header">Plat</h2></a>
    <a href="menu.php"><h2 class="text-header">Menu</h2></a>
    <a href="creation.php"><h2 class="text-header">Création</h2></a>
</header>
<body>


        <!-- ajout d'une catégorie -->
     <div class="container-form-crea1">
        <form action="" method="post" class="form-categorie-add">
            <h2>Nouvelle catégorie</h2>
            <label for="nom_categorie">Nom de la catégorie</label>
            <input type="text" name="nom_categorie" placeholder="Entrer le nom de la catégorie" autocomplete="off" class="input-champ">
            <br>
            <button type="submit" name="envoi" class="bouton-creation">Ajouter</button>
        </form>
        <!-- suppression d'une catégorie -->
        <form action="" method="post" class="form-categorie-delete">
            <h2>Supprimer une catégorie</h2>
            <label for="nom_categorie">Nom de la catégorie</label>
            <select name="nom_categorie" class="select-champ">
                <option value="">Sélectionner une catégorie</option>
                <?php
                $categories = $pdo->query('SELECT nom_categorie FROM categorie');
                while ($categorie = $categories->fetch()) {
                    echo '<option value="' . htmlspecialchars($categorie['nom_categorie']) . '">' . htmlspecialchars($categorie['nom_categorie']) . '</option>';
                }
                ?>
            </select>
            <br>
            <button type="submit" name="supprimer_menu" class="suppr">Supprimer</button>
        </form>
    </div>


        <!-- ajout d'un ingrédient -->
    <div class="container-form-crea2">
        <form action="" method="post" class="form-ingredient-add">
            <h2>Nouvelle ingrédient</h2>
            <label for="nom_ingredient">Nom de l'ingrédient</label>
            <input type="text" name="nom_ingredient" placeholder="Entrer le nom de l'ingrédient" autocomplete="off" class="input-champ">
            <br>
            <button type="submit" name="envoie" class="bouton-creation">Ajouter</button>
        </form>
        <!-- suppression d'un ingrédient -->
        <form action="" method="post" class="form-ingredient-delete">
            <h2>Supprimer un ingrédient</h2>
            <label for="nom_ingredient">Nom de l'ingrédient</label>
            <select name="nom_ingredient" class="select-champ">
            <option value="">Sélectionner un ingrédient</option>
                <?php
                $categories = $pdo->query('SELECT nom_ingredient FROM ingredient');
                while ($categorie = $categories->fetch()) {
                    echo '<option value="' . htmlspecialchars($categorie['nom_ingredient']) . '">' . htmlspecialchars($categorie['nom_ingredient']) . '</option>';
                }
                ?>
            </select>
            <br>
            <button type="submit" name="supprimé" class="suppr">Supprimer</button>
        </form>
    </div>


<!-- Formulaire pour ajouter un nouveau plat -->
    <section class="container-form-crea4">
        <form method="POST" class="form-ajout-plat">
            <h2>Ajouter un Nouveau Plat</h2>
            <input type="text" name="nom_plat" placeholder="Nom du plat" required autocomplete="off" class="input-champ-2">
            <input type="number" step="0.01" name="prix_plat" placeholder="Prix du plat" required autocomplete="off" class="input-champ-2">
            <textarea name="description_plat" placeholder="Description du plat" required autocomplete="off" class="input-champ-2"></textarea>

            <!-- Remplacer le champ file par un champ text pour l'URL de l'image -->
            <input type="text" name="image_plat" placeholder="URL de l'image" required autocomplete="off" class="input-champ-2">

            <!-- Sélectionner une catégorie existante -->
            <label for="nom_categorie" class="label-categorie">Catégorie :</label>
            <select name="nom_categorie" required class="select-champ-2">
                <option value="">Sélectionner une catégorie</option>
                <?php
                $categories = $pdo->query('SELECT nom_categorie FROM categorie');
                while ($categorie = $categories->fetch()) {
                    echo '<option value="' . htmlspecialchars($categorie['nom_categorie']) . '">' . htmlspecialchars($categorie['nom_categorie']) . '</option>';
                }
                ?>
            </select>

            <button type="submit" name="ajouter_plat" class="bouton-plat">Ajouter Plat</button>
        </form>
        <form action="" method="post" class="form-plat-delete">
            <h2>Supprimer un plat</h2>
            <label for="nom_plat">Nom du plat</label>
            <select name="nom_plat" class="select-champ">
            <option value="">Sélectionner un plat</option>
                <?php
                $categories = $pdo->query('SELECT nom_plat FROM plat');
                while ($categorie = $categories->fetch()) {
                    echo '<option value="' . htmlspecialchars($categorie['nom_plat']) . '">' . htmlspecialchars($categorie['nom_plat']) . '</option>';
                }
                ?>
            </select>
            <br>
            <button type="submit" name="supprimé" class="suppr">Supprimer</button>
        </form>
    </section>
</body>
<footer>
    <p class="text-footer1">© Touts droits réservé ©</p>
    <p class="text-footer2">Site de restauration</p>
</footer>
</html>