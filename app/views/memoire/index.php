<?php


    $nom = $_SESSION['nom'] ?? "User";
    $prenom = $_SESSION['prenom'] ?? "File";
    $initiales = strtoupper($nom[0] . ($prenom[0] ?? ''));

    

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceuil Etudiant</title>
    <link rel="stylesheet" href="/Mon document/Gestion_memoire/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <h2>📚 GASA-Archive</h2>
            <small>Plateforme universitaire</small>
        </div>
        <!-- RECHERCHE -->
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Rechercher par titre, filière, professeur...">
        </div>
        <div class="menu">
            <a href="notifications.php" class="icon">
                <i class="fa-solid fa-bell"></i>
            </a>
            <a href="depot.php" class="btn-depot">
                <i class="fa-solid fa-upload"></i> Déposer un mémoire
            </a>
            <a href="profil.php" class="profile">
                <div class="avatar">
                    <?= $initiales ?>
                </div>
                <span><?= $nom . " " . $prenom ?></span>
            </a>
        </div>
    </header>

    <main>
        <!-- DASHBOARD -->
        <div class="dashboard">
            <!-- mémoires -->
            <div class="dashboard-card">
                <h1><?= $totalMemoires ?></h1>
                <p>Mémoires publiés</p>
            </div>
            <!-- filières -->
            <div class="dashboard-card">
                <h1><?= $totalFilieres ?></h1>
                <p>Filières couvertes</p>
            </div>
            <!-- centres -->
            <div class="dashboard-card">
                <h1>8</h1>
                <p>Centres</p>
            </div>
        </div>

        <!-- FILTRES -->
        <div class="filtres">
            <a href="?niveau=Tous" class="filtre-btn <?= ($niveau == 'Tous') ? 'active' : '' ?>">Tous</a>
            <a href="?niveau=L3" class="filtre-btn <?= ($niveau == 'L3') ? 'active' : '' ?>">Licence 3</a>
            <a href="?niveau=M2" class="filtre-btn <?= ($niveau == 'M2') ? 'active' : '' ?>">Master 2</a>
        </div>

        <!-- PUBLICATIONS -->
         <h2 class="section-title">
            Mémoires récents
        </h2>
        <div class="publications">
            <?php foreach($publications as $pub): ?>
                <div class="publication-card">

                    <!-- HEADER -->
                    <div class="publication-header">
                        <span class="badge-filiere">
                            <?= $pub['niveau'] ?>
                        </span>
                        <span class="annee">
                            <?= $pub['annee_academique'] ?>
                        </span>
                    </div>

                    <!-- BODY -->
                    <div class="publication-body">
                        <h3>
                            <?= $pub['theme'] ?>
                        </h3>
                        <p class="auteur">
                            Par
                            <?= $pub['nomAut'] ?>
                            <?= $pub['prenomAut'] ?>
                        </p>
                        <p class="info">
                            <i class="fa-solid fa-book"></i>
                            <?= $pub['idfiliere'] ?>
                        </p>
                        <p class="info">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= $pub['centre'] ?>
                        </p>
                        <p class="info">
                            <i class="fa-solid fa-user-graduate"></i>
                            Dir. :
                            <?= $pub['maitre_memoire'] ?>
                        </p>
                    </div>

                    <!-- FOOTER -->
                    <div class="publication-footer">
                        <div class="stats">
                            <span class="likes">
                                <?php
                                    $totalLikes = $likeModel->countLikes($pub['idAM']);
                                    $isLiked = $likeModel->isLiked(
                                        $pub['idAM'],
                                        $_SESSION['idetudiant']
                                    );
                                ?>
                                <a href="/Gestion_memoire/public/like.php?id=<?= $pub['idAM'] ?>" class="likes">
                                    <i class="fa-solid fa-heart
                                    <?= $isLiked ? 'liked' : '' ?>"></i>
                                    <?= $totalLikes ?>
                                </a>
                            </span>
                            <span class="comments">
                                <i class="fa-solid fa-message"></i>
                                11
                            </span>
                        </div>
                        <a href="#" class="btn-voir">
                            Voir
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>