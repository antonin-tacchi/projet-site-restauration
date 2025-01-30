<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=site_restauration", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer tous les plats avec leurs catégories associées
$query_plats = $conn->query("SELECT plat.*, GROUP_CONCAT(categorie.nom_categorie) AS categories
                             FROM plat
                             LEFT JOIN categorie_plat ON plat.id = categorie_plat.id_plat
                             LEFT JOIN categorie ON categorie_plat.id_categorie = categorie.id
                             GROUP BY plat.id");
$plats = $query_plats->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories
$query_categories = $conn->query("SELECT nom_categorie FROM categorie");
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- Affichage des plats existants -->
    <section>
        <h2>Plats Disponibles</h2>
        <?php if (count($plats) > 0): ?>
            <ul>
                <?php foreach ($plats as $plat): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($plat['nom_plat']); ?></h3>
                        <p>Prix : <?php echo htmlspecialchars($plat['prix']); ?>€</p>
                        <p>Description : <?php echo htmlspecialchars($plat['description_plat']); ?></p>
                        <p>Catégorie : <?php echo htmlspecialchars($plat['categories']); ?></p>
                        <?php if ($plat['image_plat']): ?>
                            <img src="<?php echo htmlspecialchars($plat['image_plat']); ?>" alt="<?php echo htmlspecialchars($plat['nom_plat']); ?>" width="200">
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun plat disponible pour le moment.</p>
        <?php endif; ?>
    </section>
</main>

<!-- Footer -->
<footer>
    <p>© 2025 Restaurant</p>
</footer>

</body>
</html>
