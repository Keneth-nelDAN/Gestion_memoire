

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes mémoires</title>
    <link rel="stylesheet" href="/Mon document/Gestion_memoire/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php require "../app/views/includes/header.php"; ?>
    <div class="mes-memoires">

        <a href="index.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
        </a>
        <h2>Mes mémoires</h2>

        <table class="table-memoires">

            <thead>
                <tr>
                    <th>Thème</th>
                    <th>Date de dépôt</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($memoires as $memoire): ?>

                <tr>

                    <td><?= $memoire['theme'] ?></td>

                    <td><?= $memoire['date_depot'] ?></td>

                    <td>

                        <?php if($memoire['statut'] == 'publie'): ?>

                            <span class="statut-publie">
                                Publié
                            </span>

                        <?php elseif($memoire['statut'] == 'en_attente'): ?>

                            <span class="statut-en-attente">
                                En attente
                            </span>

                        <?php else: ?>

                            <span class="statut-refuse">
                                Refusé
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</body>
</html>