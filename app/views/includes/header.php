<?php
$etudiantNiveau = $etudiant['nomNiveau'] ?? $_SESSION['nomNiveau'] ?? '';
$showActions = in_array($etudiantNiveau, ['L3', 'M2']);
?>
<header class="navbar">
        <div class="logo">
            <h2>📚  GénieMémoire</h2>
            <small>Plateforme universitaire</small>
        </div>
        <!-- RECHERCHE -->
        <!--<div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Rechercher par titre, filière, professeur...">
        </div>-->
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
                    <?= strtoupper(substr($etudiant['nom'],0,1)) .
                    strtoupper(substr($etudiant['prenom'],0,1)) ?>
                </div>
                <span><?= $etudiant['nom'] . " " . $etudiant['prenom'] ?></span>
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