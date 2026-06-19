﻿﻿﻿<?php
session_start();
// 1. Inclusion des configurations et des fonctions d'aide
require_once __DIR__ . '/../../controllers/publicationController.php';

// Note: Toute la logique de traitement a été déplacée dans PublicationController.
// Cette page ne fait plus que de l'affichage.

$publicationController = new PublicationController();
$sharedData = $publicationController->getSharedData();
$filieres = $sharedData['filieres'];
$centres = $sharedData['centres'];

$active_page = 'publications';
$nom_de = $_SESSION['user']['nom'] ?? 'Direction';
$initiales_de = strtoupper(substr($_SESSION['user']['prenom'] ?? 'D', 0, 1) . substr($_SESSION['user']['nom'] ?? 'E', 0, 1));

// Récupération des messages flash depuis la session
$success = $_SESSION['flash']['success'] ?? null;
$error = $_SESSION['flash']['error'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Publier plusieurs lots</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>
    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Publication par lot</span>
                <h1>Publier plusieurs mémoires</h1>
                <p>Envoyez plusieurs fichiers et créez automatiquement un mémoire par fichier.</p>
            </div>
            <a class="btn-gold" href="publier_memoire.php"><i class="fa-solid fa-file-circle-plus"></i> Publier un mémoire</a>
        </header>
        <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form class="publication-card large" action="/Gestion_memoire/public/publication/create-batch" method="post" enctype="multipart/form-data">
            <div class="form-title"><i class="fa-solid fa-layer-group"></i><div><h2>Informations communes du lot</h2><p>Chaque fichier créera une ligne dans les anciens mémoires.</p></div></div>
            <label>Fichiers</label><input type="file" name="fichiers[]" accept="application/pdf" multiple required>
            <small class="hint">Les titres peuvent être déduits des noms de fichiers si la liste ci-dessous est vide.</small>
            <label>Titres, un par ligne</label><textarea name="batch_titres" rows="5" placeholder="Titre du fichier 1&#10;Titre du fichier 2"></textarea>
            <label>Auteurs, un par ligne</label><textarea name="batch_auteurs" rows="4" placeholder="Prénom Nom&#10;Prénom Nom"></textarea>
            <div class="field-row">
                <div><label>Filière commune</label><select name="batch_idfiliere" required><option value="">Sélectionner</option><?php foreach ($filieres as $filiere): ?><option value="<?= (int) $filiere['idfiliere'] ?>"><?= htmlspecialchars($filiere['nom_filiere'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                <div><label>Centre commun</label><select name="batch_idCentre"><option value="">Non défini</option><?php foreach ($centres as $centre): ?><option value="<?= (int) $centre['idCentre'] ?>"><?= htmlspecialchars($centre['nomCentre'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="field-row"><div><label>Année académique</label><input name="batch_annee" placeholder="2025-2026"></div><div><label>Maître mémoire commun</label><input name="batch_maitre"></div></div>
            <label>Examinateur commun</label><input name="batch_examinateur">
            <label>Président du jury commun</label><input name="batch_president">
            <button class="btn-blue full" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Publier le lot</button>
        </form>
    </main>
</div>
</body>
</html>