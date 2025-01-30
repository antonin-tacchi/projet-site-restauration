<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=site_restauration", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer tous les menus avec leurs plats associés
$query = $conn->query("SELECT * FROM menu");
$menus = $query->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les plats pour chaque menu
$plats = [];
foreach ($menus as $menu) {
    $query_plats = $conn->prepare("SELECT * FROM plat WHERE FIND_IN_SET(id, ?)");
    $query_plats->execute([$menu['plat_menu']]);
    $plats[$menu['id']] = $query_plats->fetchAll(PDO::FETCH_ASSOC);
}

// Ajouter un menu (si formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_menu'])) {
    $nom_menu = $_POST['nom_menu'];
    $prix_menu = $_POST['prix_menu'];
    $plats_menu = implode(',', $_POST['plats_menu']);  // Enregistrer les plats sous forme de chaîne séparée par des virgules

    // Insérer le menu dans la base de données
    $stmt = $conn->prepare("INSERT INTO menu (nom_menu, prix_menu, plat_menu) VALUES (?, ?, ?)");
    $stmt->execute([$nom_menu, $prix_menu, $plats_menu]);

    // Rediriger après l'ajout du menu pour éviter la duplication à cause du rechargement de la page
    header("Location: " . $_SERVER['PHP_SELF']); // Redirection vers la même page
    exit(); // S'assurer que le script s'arrête après la redirection
}

// Récupérer toutes les catégories
$query_categories = $conn->query("SELECT * FROM categorie");
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les plats d'une catégorie spécifique
if (isset($_POST['categorie_menu'])) {
    $categorie_id = $_POST['categorie_menu'];
    $query_plats = $conn->prepare("
        SELECT plat.id, plat.nom_plat 
        FROM plat
        JOIN categorie_plat ON plat.id = categorie_plat.id_plat
        WHERE categorie_plat.id_categorie = ?");
    $query_plats->execute([$categorie_id]);
    $plats_disponibles = $query_plats->fetchAll(PDO::FETCH_ASSOC);
} else {
    $plats_disponibles = [];
}
// suppresion de menu
if (isset($_POST['supprimé'])) {
    if (isset($_POST['nom_menu']) and !empty($_POST['nom_menu'])) {
        $nom_menu = htmlspecialchars($_POST['nom_menu']);
        $recupmenu = $conn->prepare('SELECT * FROM menu WHERE nom_menu = ?');
        $recupmenu->execute(array($nom_menu));

        if ($recupmenu->rowCount() > 0) {
            $deletemenu = $conn->prepare('DELETE FROM menu WHERE nom_menu = ?');
            $deletemenu->execute(array($nom_menu));
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant</title>
</head>
<body>

<!-- Header -->
<header>
    <h1>Bienvenue au Restaurant</h1>
</header>

<!-- Body -->
<main>
    <!-- Affichage des cartes de menu -->
    <section>
        <h2>Menus Disponibles</h2>
        <?php foreach ($menus as $menu): ?>
            <div class="menu-card">
                <h3><?php echo htmlspecialchars($menu['nom_menu']); ?></h3>
                <p>Prix : <?php echo htmlspecialchars($menu['prix_menu']); ?>€</p>

                <!-- Affichage des plats associés au menu -->
                <h4>Plats :</h4>
                <ul>
                    <?php foreach ($plats[$menu['id']] as $plat): ?>
                        <li>
                            <p><strong><?php echo htmlspecialchars($plat['nom_plat']); ?></strong></p>
                            <img src="<?php echo htmlspecialchars($plat['image_plat']); ?>" alt="<?php echo htmlspecialchars($plat['nom_plat']); ?>" width="200">
                            <p><em>Description : <?php echo htmlspecialchars($plat['description_plat']); ?></em></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
        <?php endforeach; ?>
    </section>

    <!-- Formulaire pour ajouter un nouveau menu -->
    <section>
        <h2>Ajouter un Nouveau Menu</h2>
        <form method="POST">
            <input type="text" name="nom_menu" placeholder="Nom du menu" required>
            <input type="number" step="0.01" name="prix_menu" placeholder="Prix" required>

            <label for="categorie_menu">Choisissez une catégorie :</label>
            <select name="categorie_menu" onchange="this.form.submit()" required>
                <option value="">Sélectionner une catégorie</option>
                <?php foreach ($categories as $categorie): ?>
                    <option value="<?php echo $categorie['id']; ?>" <?php if (isset($_POST['categorie_menu']) && $_POST['categorie_menu'] == $categorie['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($categorie['nom_categorie']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($plats_disponibles)): ?>
                <label for="plats_menu">Plats disponibles :</label>
                <select name="plats_menu[]" multiple required>
                    <?php foreach ($plats_disponibles as $plat): ?>
                        <option value="<?php echo $plat['id']; ?>"><?php echo htmlspecialchars($plat['nom_plat']); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <button type="submit" name="ajouter_menu">Ajouter Menu</button>
        </form>
        <form action="" method="post" class="form-menu-delete">
            <h2>Supprimer un menu</h2>
            <label for="nom_menu">Nom du plat</label>
            <select name="nom_menu" class="select-champ">
            <option value="">Sélectionner un menu</option>
                <?php
                $categories = $conn->query('SELECT nom_menu FROM menu');
                while ($categorie = $categories->fetch()) {
                    echo '<option value="' . htmlspecialchars($categorie['nom_menu']) . '">' . htmlspecialchars($categorie['nom_menu']) . '</option>';
                }
                ?>
            </select>
            <br>
            <button type="submit" name="supprimé" class="suppr">Supprimer</button>
        </form>
    </section>
</main>


<!-- Footer -->
<footer>
    <p>© 2025 Restaurant</p>
</footer>

</body>
</html>
