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

// Supprimer un menu
if (isset($_GET['delete_menu'])) {
    $menu_id = $_GET['delete_menu'];

    // Supprimer le menu de la table 'menu'
    $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
    $stmt->execute([$menu_id]);

    // Rediriger après la suppression pour éviter un rechargement de la page avec une nouvelle requête GET
    header("Location: " . $_SERVER['PHP_SELF']); // Redirection vers la même page
    exit();
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
                            <!-- Affichage du titre du plat au-dessus de l'image -->
                            <p><strong><?php echo htmlspecialchars($plat['nom_plat']); ?></strong></p>

                            <!-- Affichage de l'image sous le titre -->
                            <img src="images/<?php echo htmlspecialchars($plat['image_plat']); ?>" alt="<?php echo htmlspecialchars($plat['nom_plat']); ?>" width="200">

                            <!-- Description sous l'image -->
                            <p><em>Description : <?php echo htmlspecialchars($plat['description_plat']); ?></em></p>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Bouton de suppression du menu -->
                <a href="?delete_menu=<?php echo $menu['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ?');">
                    <button>Supprimer Menu</button>
                </a>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Formulaire pour ajouter un nouveau menu -->
    <section>
        <h2>Ajouter un Nouveau Menu</h2>
        <form method="POST">
            <input type="text" name="nom_menu" placeholder="Nom du menu" required>
            <input type="number" step="0.01" name="prix_menu" placeholder="Prix" required>
            
            <label for="plats_menu">Plats inclus :</label>
            <select name="plats_menu[]" multiple required>
                <?php
                // Afficher tous les plats disponibles
                $query_plats_disponibles = $conn->query("SELECT * FROM plat");
                while ($plat = $query_plats_disponibles->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$plat['id']}'>{$plat['nom_plat']}</option>";
                }
                ?>
            </select>

            <button type="submit" name="ajouter_menu">Ajouter Menu</button>
        </form>
    </section>
</main>

<!-- Footer -->
<footer>
    <p>© 2025 Restaurant</p>
</footer>

</body>
</html>
