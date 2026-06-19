﻿﻿﻿<?php
session_start();
require_once __DIR__ . '/../../controllers/publicationController.php';

// Note: La logique de traitement du formulaire (POST) a été déplacée
// vers un contrôleur pour respecter l'architecture MVC.
// Ce fichier ne gère plus que l'affichage.

$publicationController = new PublicationController();
$sharedData = $publicationController->getSharedData();
$filieres = $sharedData['filieres'];
$centres = $sharedData['centres'];

$active_page = 'publication';
$nom_de = $_SESSION['user']['nom'] ?? 'Direction';
$initiales_de = strtoupper(substr($_SESSION['user']['prenom'] ?? 'D', 0, 1) . substr($_SESSION['user']['nom'] ?? 'E', 0, 1));

// Les variables $success et $error seraient passées par le contrôleur
$success = $_SESSION['flash']['success'] ?? null;
$error = $_SESSION['flash']['error'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Publier un mémoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>
    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Publication</span>
                <h1>Publier un mémoire</h1>
                <p>Ajoutez un mémoire unique dans la table des anciens mémoires.</p>
            </div>
            <a class="btn-blue" href="publier_lots.php"><i class="fa-solid fa-layer-group"></i> Publier plusieurs lots</a>
        </header>

        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <form class="publication-card large" action="/Gestion_memoire/public/publication/create" method="post" enctype="multipart/form-data">
            <div class="form-title">
                <i class="fa-solid fa-file-circle-plus"></i>
                <div>
                    <h2>Informations du mémoire</h2>
                    <p>Renseignez les métadonnées avant publication.</p>
                </div>
            </div>
            <label>Thème du mémoire</label>
            <textarea name="theme" rows="3" required></textarea>
            <div class="field-row">
                <div><label>Prénom auteur(s)</label><input name="prenomAut"></div>
                <div><label>Nom auteur(s)</label><input name="nomAut" required></div>
            </div>
            <div class="field-row">
                <div>
                    <label>Filière</label>
                    <select name="idfiliere" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Centre</label>
                    <select name="idCentre">
                        <option value="">Non défini</option>
                        <?php foreach ($centres as $centre): ?>
                            <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div><label>Année académique</label><input name="annee_academique" placeholder="2025-2026"></div>
                <div><label>Statut</label><select name="statut"><option value="publie">Publié</option><option value="en_attente">Validé non publié</option><option value="brouillon">Brouillon</option></select></div>
            </div>
            <label>Maître mémoire</label><input name="maitre_memoire">
            <label>Examinateur</label><input name="examinateur">
            <label>Président du jury</label><input name="president_jury">
            <label>Fichier</label><input type="file" name="fichier" accept="application/pdf" required>
            <button class="btn-gold full" type="submit"><i class="fa-solid fa-paper-plane"></i> Publier ce mémoire</button>
        </form>
    </main>
</div>
</body>
</html>