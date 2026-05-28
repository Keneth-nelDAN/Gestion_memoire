<?php
session_start();

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
    </main>

</body>
</html>