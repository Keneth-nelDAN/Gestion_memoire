<?php
session_start();

// Vérifier que l'utilisateur est un directeur
if (empty($_SESSION['idde']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'directeur')) {
    header('Location: /Gestion_memoire/public/login.php');
    exit;
}

// La logique de traitement a été déplacée dans le contrôleur.
// La vue se contente d'afficher les données.
require_once __DIR__ . '/../../controllers/professeurController.php';
$professeurController = new ProfesseurController();

$de_profile = $professeurController->getDEProfile();
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];
$active_page = 'professeurs';

$success = $_SESSION['flash']['success'] ?? null;
$error = $_SESSION['flash']['error'] ?? null;
$generated_password = $_SESSION['flash']['password'] ?? null;
unset($_SESSION['flash']);

$default_password = 'Prof@' . random_int(1000, 9999);

$professeurs = $professeurController->getAll();
$nb_professeurs = count($professeurs);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Ajouter professeur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Gestion des accès</span>
                <h1>Ajouter professeur</h1>
                <p>Créez les comptes enseignants qui pourront se connecter à l'espace professeur.</p>
            </div>
            <a class="btn-blue" href="/Gestion_memoire/public/dashboard"><i class="fa-solid fa-arrow-left"></i> Tableau de bord</a>
        </header>

        <?php if ($success): ?>
            <div class="alert success">
                <?= e($success) ?>
                <?php if ($generated_password): ?><br>Mot de passe initial : <strong><?= e($generated_password) ?></strong><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <section class="professor-layout">
            <form class="publication-card professor-form" action="/Gestion_memoire/public/professeurs" method="post" autocomplete="off">
                <div class="form-title">
                    <i class="fa-solid fa-user-check"></i>
                    <div>
                        <h2>Nouveau compte professeur</h2>
                        <p>Email et mot de passe serviront à la connexion du professeur.</p>
                    </div>
                </div>

                <div class="field-row">
                    <div>
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" required placeholder="Ex. Hounkpe">
                    </div>
                    <div>
                        <label for="prenom">Prénom</label>
                        <input id="prenom" name="prenom" required placeholder="Ex. Marius">
                    </div>
                </div>

                <label for="email">Email de connexion</label>
                <input id="email" type="email" name="email" required placeholder="professeur@universite.edu">

                <label for="motdepasse">Mot de passe initial</label>
                <input id="motdepasse" type="text" name="motdepasse" value="<?= e($default_password) ?>">
                <small class="hint">Communiquez ce mot de passe au professeur après création du compte.</small>

                <button class="btn-gold full" type="submit"><i class="fa-solid fa-user-plus"></i> Créer le compte professeur</button>
            </form>

            <aside class="access-summary">
                <div class="summary-card">
                    <span class="summary-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                    <p>Comptes professeurs</p>
                    <strong><?= (int) $nb_professeurs ?></strong>
                    <small>Professeurs enregistrés</small>
                </div>
                <div class="summary-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Le DE ajoute uniquement les professeurs. Les informations créées ici sont celles utilisées pour leur connexion.</p>
                </div>
            </aside>
        </section>

        <section class="table-container professor-table">
            <div class="table-header">
                <div>
                    <h2>Professeurs ajoutés</h2>
                    <p>Liste des comptes actuellement autorisés à accéder à l'espace professeur.</p>
                </div>
            </div>
            <div class="teacher-list">
                <?php if (!empty($professeurs)): ?>
                    <?php foreach ($professeurs as $prof): ?>
                        <article class="teacher-item">
                            <div class="teacher-avatar"><?= e(strtoupper(substr($prof['prenom'], 0, 1) . substr($prof['nom'], 0, 1))) ?></div>
                            <div>
                                <strong><?= e($prof['prenom'] . ' ' . $prof['nom']) ?></strong>
                                <span><?= e($prof['email']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Aucun professeur ajouté pour le moment.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
