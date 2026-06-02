<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la mémoire</title>
    <link rel="stylesheet" href="/Mon document/Gestion_memoire/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="detail-container">

        <a href="/Mon%20document/Gestion_memoire/public/" class="back-link">
            <i class="fa-solid fa-arrow-left"></i>
            Retour à l'accueil
        </a>

        <!-- ENTETE -->
        <div class="detail-header">

            <div class="auteur">
                <div class="avatar">
                    <?= strtoupper(substr($memoire['prenomAut'],0,1)) ?>
                    <?= strtoupper(substr($memoire['nomAut'],0,1)) ?>
                </div>

                <div class="infos-auteur">
                    <h4>
                        <?= $memoire['prenomAut'] ?>
                        <?= $memoire['nomAut'] ?>
                    </h4>

                    <div class="infos-secondaires">

                        <span><?= $memoire['centre'] ?></span>

                        <span><?= $memoire['nom_filiere'] ?></span>

                        <span><?= $memoire['niveau'] ?></span>

                        <span><?= $memoire['annee_academique'] ?></span>

                    </div>
                </div>
            </div>

            <h3 class="theme">
                <?= $memoire['theme'] ?>
            </h3>

        </div>

        <!-- CONTENU -->
        <div class="contenu-detail">

            <!-- PDF -->
            <div class="pdf-section">

                <iframe
                    src="/Mon%20document/Gestion_memoire/public/view_pdf.php?id=<?= $memoire['idAM'] ?>#toolbar=0"
                    width="100%"
                    height="800">
                </iframe>

            </div>

                        <!-- JURY -->
            <div class="sidebar">
                <div class="jury">

                    <h3>Composition du jury</h3>

                    <div class="membre-jury">
                        <strong>Maître mémoire</strong>
                        <p><?= $memoire['maitre_memoire'] ?></p>
                    </div>

                    <div class="membre-jury">
                        <strong>Examinateur</strong>
                        <p><?= $memoire['examinateur'] ?></p>
                    </div>

                    <div class="membre-jury">
                        <strong>Président du jury</strong>
                        <p><?= $memoire['president_jury'] ?></p>
                    </div>

                </div>

                <!-- LIKES + COMMENTAIRES -->
                <div class="stats">
                                    <span class="likes">
                                        <?php
                                            $totalLikes = $likeModel->countLikes($memoire['idAM']);
                                            $isLiked = $likeModel->isLiked(
                                                $memoire['idAM'],
                                                $_SESSION['idetudiant']
                                            );
                                        ?>
                                        <a href="/Mon%20document/Gestion_memoire/public/like.php?id=<?= $memoire['idAM'] ?>" class="likes ajax-like" data-id="<?= $memoire['idAM'] ?>">
                                            <i class="fa-solid fa-heart <?= $isLiked ? 'liked' : '' ?>"></i>
                                            <?= $totalLikes ?>
                                        </a>
                                    </span>
                                    <span class="comments">
                                        <?php
                                            $totalCommentaires = $commentaireModel->countCommentaires($memoire['idAM']);
                                        ?>

                                        <a href="/Mon%20document/Gestion_memoire/public/detail.php?id=<?= $memoire['idAM'] ?>" class="commentaires">
                                            <i class="fa-solid fa-comment"></i>
                                            <?= $totalCommentaires ?>
                                        </a>
                                    </span>
                </div>

                <!-- LISTE COMMENTAIRES -->
                <div class="commentaires-section">

                <h3>
                    Commentaires (<?= $totalCommentaires ?>)
                </h3>

                <div class="commentaires-liste">

                    <?php if(empty($commentaires)): ?>
                        <div class="commentaire empty-comments">
                            Aucun commentaire pour le moment.
                        </div>
                    <?php endif; ?>

                    <?php foreach($commentaires as $commentaire): ?>

                        <div class="commentaire">

                            <div class="commentaire-header">

                                <div class="avatar-comment">
                                    <?= strtoupper(substr($commentaire['nom'],0,1)) ?>
                                </div>

                                <div>
                                    <div class="commentaire-nom">
                                        <?= $commentaire['nom'] ?>
                                        <?= $commentaire['prenom'] ?>
                                    </div>

                                    <div class="commentaire-date">
                                        <?= $commentaire['date_commentaire'] ?>
                                    </div>
                                </div>

                            </div>

                            <div class="commentaire-contenu">
                                <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <?php if($nombrePagesCommentaires > 1): ?>
                    <div class="commentaires-pagination">
                        <?php for($i = 1; $i <= $nombrePagesCommentaires; $i++): ?>
                            <a href="?id=<?= $memoire['idAM'] ?>&page=<?= $i ?>" class="comment-page-link <?= ($pageComments == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                </div>

                <!-- FORMULAIRE -->
                <div class="ajout-commentaire">

                    <form action="/Mon%20document/Gestion_memoire/public/commentaire.php" method="POST">
                        <input type="hidden" name="idAM" value="<?= $memoire['idAM'] ?>">
                        <textarea name="contenu" required placeholder="Ajouter un commentaire..."></textarea>

                        <button type="submit">
                            Envoyer
                        </button>
                    </form>
                </div>

            </div>
        </div>


    </div>

    <script src="/Mon%20document/Gestion_memoire/public/assets/js/script.js"></script>
</body>
</html>