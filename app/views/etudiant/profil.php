<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="/Mon document/Gestion_memoire/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php require "../app/views/includes/header.php"; ?>
    <div class="profil">
        <div class="container-profil">
            <a href="index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
            </a>
            <h2>Mon profil</h2>
            <div class="infos-profil">
                <p>
                    <strong>Nom :</strong>
                    <?= $etudiant['nom'] ?>
                </p>
                <p>
                    <strong>Prénom :</strong>
                    <?= $etudiant['prenom'] ?>
                </p>
                <p>
                    <strong>Email :</strong>
                    <?= $etudiant['email'] ?>
                </p>
                <p>
                    <strong>Filière :</strong>
                    <?= $etudiant['nom_filiere'] ?>
                </p>
                <p>
                    <strong>Niveau :</strong>
                    <?= $etudiant['nomNiveau'] ?>
                </p>
                <p>
                    <strong>Centre :</strong>
                    <?= $etudiant['nomCentre'] ?>
                </p>
                <p>
                    <strong>Année Académique :</strong>
                    <?= $etudiant['annee'] ?>
                </p>
            </div>
        </div>
    </div>

</body>
</html>