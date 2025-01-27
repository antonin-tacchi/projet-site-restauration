<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=site-restauration', 'root', '');


if(isset($_POST['envoi'])){
    if(isset($_POST['username']) and !empty($_POST['password'])){
        $nom_utilisateur = htmlspecialchars($_POST['username']);
        $recup_user = $bdd->prepare('SELECT * FROM utilisateur WHERE nom_utilisateur = ? and mdp = ?');
        $recup_user->execute(array($nom_utilisateur, $_POST['password']));

        if($recup_user->rowCount() > 0){
                $_SESSION['username'] = $nom_utilisateur;
                $_SESSION['mdp'] = $_POST['password'];
                $_SESSION['id'] = $recup_user->fetch()['id'];
                header('Location: menu.php');
        }
        else{
            echo "Nom d'utilisateur ou mot de passe incorrect";
        }
    }
    else{
        echo "Veuillez remplir tous les champs";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
    <form action="" method="post" align="center">
        <label for="username">Nom d'utilisateur</label>
        <br>
        <input type="text" name="username" autocomplete="off">
        <br>
        <label for="password">Mot de passe</label>
        <br>
        <input type="password" name="password" autocomplete="off">
        <br><br>
        <input type="submit" name="envoi">
    </form>
</body>
</html>