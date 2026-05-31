<?php
// Variables disponibles : $prof, $memoires, $decisions, $idprof
$nomProf = htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professeur - Gestion des Mémoires</title>
    <link rel="stylesheet" href="/Gestion_memoire/public/assets/css/style.css">
    <style>
        /* Vous pouvez copier ici les styles du code fourni ou les mettre dans votre fichier CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; color: #333; }
        .container { max-width: 1400px; margin: 0 auto; }
        header { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        header h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .logout-btn { float: right; background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; color: white; text-decoration: none; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
        .section-title { font-size: 1.5rem; margin: 25px 0 15px; padding-bottom: 8px; border-bottom: 3px solid #2a5298; display: inline-block; }
        .memoires-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 25px; margin-top: 20px; }
        .carte-memoire { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
        .entete-memoire { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #e9ecef; }
        .theme { font-size: 1.2rem; font-weight: 600; color: #1e3c72; margin-bottom: 8px; }
        .meta { font-size: 0.85rem; color: #6c757d; display: flex; flex-wrap: wrap; gap: 12px; }
        .badge-role { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; background: #ffd700; color: #856404; }
        .contenu-memoire { padding: 20px; }
        .info-ligne { margin-bottom: 10px; font-size: 0.9rem; }
        .info-ligne strong { width: 130px; display: inline-block; color: #495057; }
        .decision-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; margin-top: 10px; }
        .decision-accepte { background: #d4edda; color: #155724; }
        .decision-refuse { background: #f8d7da; color: #721c24; }
        .decision-aucune { background: #e2e3e5; color: #383d41; }
        .commentaire-box { background: #f8f9fa; padding: 12px; border-radius: 10px; margin-top: 15px; border-left: 4px solid #ffc107; font-size: 0.9rem; }
        .form-decision { margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; }
        .form-group { margin-bottom: 12px; }
        label { font-weight: 500; display: block; margin-bottom: 5px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 8px; resize: vertical; }
        .btn-group { display: flex; gap: 12px; margin-top: 10px; }
        .btn { padding: 8px 18px; border: none; border-radius: 25px; cursor: pointer; font-weight: 600; }
        .btn-accept { background: #28a745; color: white; }
        .btn-refuse { background: #dc3545; color: white; }
        .btn-modify { background: #ffc107; color: #212529; text-decoration: none; display: inline-block; }
        .pdf-link { color: #2a5298; text-decoration: none; font-weight: 500; }
        .footer-actions { margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
        <h1>📘 Espace Professeur</h1>
        <p>Bienvenue, <strong><?= $nomProf ?></strong></p>
        <p>Rôle : Président du jury</p>
    </header>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (empty($memoires)): ?>
        <div class="alert alert-error" style="background:#e2e3e5; color:#383d41;">Aucun mémoire ne vous est associé en tant que président du jury.</div>
    <?php else: ?>
        <div class="memoires-grid">
            <?php foreach ($memoires as $memoire):
                $decisionActuelle = $decisions[$memoire['idmemoire']] ?? null;
            ?>
            <div class="carte-memoire">
                <div class="entete-memoire">
                    <div class="theme"><?= htmlspecialchars($memoire['theme']) ?></div>
                    <div class="meta">
                        <span>📅 <?= date('d/m/Y', strtotime($memoire['datesoumission'])) ?></span>
                        <span>👤 <?= htmlspecialchars($memoire['etudiant_nom'] . ' ' . $memoire['etudiant_prenom']) ?></span>
                    </div>
                    <div class="badge-role">👑 Président du jury</div>
                </div>
                <div class="contenu-memoire">
                    <div class="info-ligne"><strong>Filière :</strong> <?= htmlspecialchars($memoire['filiere_nom'] ?? 'Non définie') ?></div>
                    <div class="info-ligne"><strong>Centre :</strong> <?= htmlspecialchars($memoire['centre_nom'] ?? $memoire['centre']) ?></div>
                    <div class="info-ligne"><strong>Année académique :</strong> <?= htmlspecialchars($memoire['annee_academique'] ?? $memoire['annee_academique']) ?></div>
                    <div class="info-ligne"><strong>Président :</strong> <?= htmlspecialchars($memoire['president_jury']) ?></div>

                    <div class="decision-badge <?= $decisionActuelle ? ($decisionActuelle['decision'] === 'accepte' ? 'decision-accepte' : 'decision-refuse') : 'decision-aucune' ?>">
                        <?php if ($decisionActuelle): ?>
                            <?= $decisionActuelle['decision'] === 'accepte' ? '✅ Décision : ACCEPTÉ' : '❌ Décision : REFUSÉ' ?>
                            - le <?= date('d/m/Y H:i', strtotime($decisionActuelle['date_decision'])) ?>
                        <?php else: ?>
                            ⏳ Décision : En attente du président
                        <?php endif; ?>
                    </div>

                    <?php if ($decisionActuelle && !empty($decisionActuelle['observation'])): ?>
                        <div class="commentaire-box">
                            <strong>✏️ Votre commentaire :</strong><br>
                            <?= nl2br(htmlspecialchars($decisionActuelle['observation'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="footer-actions">
                        <?php if (!empty($memoire['fichier_memoire'])): ?>
                            <a href="#" class="pdf-link" onclick="window.open('uploads/<?= htmlspecialchars($memoire['fichier_memoire']) ?>', '_blank'); return false;">📄 Visualiser le mémoire</a>
                        <?php else: ?>
                            <span class="pdf-link" style="color:gray;">Aucun fichier</span>
                        <?php endif; ?>
                    </div>

                    <!-- Actions du président -->
                    <div class="form-decision">
                        <?php if (!$decisionActuelle): ?>
                            <form method="POST" action="index.php?controller=professeur&action=traiterDecision" onsubmit="return validerRefus(this);">
                                <input type="hidden" name="id_memoire" value="<?= $memoire['idmemoire'] ?>">
                                <div class="form-group">
                                    <label>📝 Commentaire (obligatoire en cas de refus) :</label>
                                    <textarea name="commentaire" rows="3" placeholder="Ajoutez vos remarques, corrections ou appréciations..."></textarea>
                                </div>
                                <div class="btn-group">
                                    <button type="submit" name="action_memoire" value="accepter" class="btn btn-accept">✅ Accepter</button>
                                    <button type="submit" name="action_memoire" value="refuser" class="btn btn-refuse">❌ Refuser (avec corrections)</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="btn-group">
                                <a href="index.php?controller=professeur&action=annulerDecision&id=<?= $memoire['idmemoire'] ?>" class="btn btn-modify" onclick="return confirm('Annuler la décision actuelle pour pouvoir la modifier ?');">🔄 Modifier la décision</a>
                            </div>
                            <div style="margin-top: 8px; font-size:0.8rem; color:#6c757d;">Décision déjà prise. Cliquez sur "Modifier" pour changer votre avis.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function validerRefus(form) {
    let action = form.action_memoire.value;
    let commentaire = form.commentaire.value.trim();
    if (action === 'refuser' && commentaire === '') {
        alert("Le commentaire de correction est obligatoire lorsque vous refusez un mémoire.");
        return false;
    }
    if (action === 'accepter') {
        return confirm("Confirmez-vous l'acceptation de ce mémoire ?");
    }
    return true;
}
</script>
</body>
</html>