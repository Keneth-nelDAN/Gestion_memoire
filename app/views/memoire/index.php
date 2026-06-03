<?php
    $nom = $_SESSION['nom'] ?? "User";
    $prenom = $_SESSION['prenom'] ?? "File";
    $initiales = strtoupper($nom[0] . ($prenom[0] ?? ''));
    $etudiantNiveau = $etudiant['nomNiveau'] ?? $_SESSION['nomNiveau'] ?? '';
    $showActions = in_array($etudiantNiveau, ['L3', 'M2']);
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
            <h2>📚  GénieMémoire</h2>
            <small>Plateforme universitaire</small>
        </div>
        <!-- RECHERCHE -->
        <form method="GET" action="">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search ?? '') ?>"
                    placeholder="Rechercher par titre, filière, centre, ...">
                <input type="hidden" name="niveau" value="<?= htmlspecialchars($niveauParam ?? $niveau ?? 'Tous') ?>">

                <button type="submit">
                    Rechercher
                </button>
            </div>
        </form>
        <div class="menu">
            <?php if ($showActions): ?>
            <?php $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
            <a href="<?= $baseUrl ?>/depot_memoire.php" class="btn-depot">
                <i class="fa-solid fa-upload"></i> Déposer un mémoire
            </a>
            <?php endif; ?>
        </div>
        <div class="profile-menu">
            <div class="profile-btn">
                <a href="profil.php" class="profile">
                <div class="avatar">
                    <?= $initiales ?>
                </div>
                <span><?= $nom . " " . $prenom ?></span>
                </a>
            </div>


            <div class="profile-dropdown">
                <a href="profil.php">
                    Mon profil
                </a>
                <?php if ($showActions): ?>
                <a href="mes_memoires.php">
                    Mes mémoires
                </a>
                <?php endif; ?>
                <a href="logout.php" class="logout-link">
                    Déconnexion
                </a>
            </div>

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
                <h1><?= $totalCentres ?></h1>
                <p>Centres</p>
            </div>
        </div>

        <?php $searchParam = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
        <!-- FILTRES -->
        <div class="filtres">
            <a href="?niveau=Tous<?= $searchParam ?>" class="filtre-btn <?= ($niveauParam == 'Tous') ? 'active' : '' ?>">Tous</a>
            <a href="?niveau=L3<?= $searchParam ?>" class="filtre-btn <?= ($niveauParam == 'L3') ? 'active' : '' ?>">Licence 3</a>
            <a href="?niveau=M2<?= $searchParam ?>" class="filtre-btn <?= ($niveauParam == 'M2') ? 'active' : '' ?>">Master 2</a>
        </div>

        <!-- PUBLICATIONS -->
         <h2 class="section-title">
            Mémoires récents
        </h2>
        <div class="publications">
            <?php if (empty($publications)): ?>
                <p class="empty-message">Aucun mémoire trouvé pour ces critères.</p>
            <?php endif; ?>
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
                            <?= $pub['nom_filiere'] ?>
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
                                <a href="/Mon%20document/Gestion_memoire/public/like.php?id=<?= $pub['idAM'] ?>" class="likes ajax-like" data-id="<?= $pub['idAM'] ?>">
                                    <i class="fa-solid fa-heart <?= $isLiked ? 'liked' : '' ?>"></i>
                                    <?= $totalLikes ?>
                                </a>
                            </span>
                            <span class="comments">
                                <?php
                                    $totalCommentaires =
                                    $commentaireModel->countCommentaires($pub['idAM']);
                                ?>

                                <a href="/Mon%20document/Gestion_memoire/public/detail.php?id=<?= $pub['idAM'] ?>" class="commentaires">
                                    <i class="fa-solid fa-comment"></i>
                                    <?= $totalCommentaires ?>
                                </a>
                            </span>
                        </div>
                        <a href="/Mon%20document/Gestion_memoire/public/detail.php?id=<?= $pub['idAM'] ?>" target="_blank" class="btn-voir">
                            Consulter
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <div class="pagination">

        <?php for($i=1;$i<=$nombrePages;$i++): ?>

        <a href="?page=<?= $i ?>&niveau=<?= urlencode($niveau) ?><?= $searchParam ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
            <?= $i ?>
        </a>

        <?php endfor; ?>

    </div>

    <script src="/Mon%20document/Gestion_memoire/public/assets/js/script.js"></script>
</body>
</html>