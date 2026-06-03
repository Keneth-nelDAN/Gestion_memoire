<?php
if (!isset($active_page)) {
    $active_page = '';
}
if (!isset($nom_de)) {
    $nom_de = 'Directeur des Études';
}
if (!isset($initiales_de)) {
    $initiales_de = 'DE';
}
?>
<aside class="de-sidebar">
    <div class="brand-block">
        <div class="brand-icon">M</div>
        <div>
            <strong>GénieMémoire</strong>
            <span>Espace Direction</span>
        </div>
    </div>

    <div class="profile-block">
        <div class="profile-avatar"><?= htmlspecialchars($initiales_de) ?></div>
        <div>
            <strong><?= htmlspecialchars($nom_de) ?></strong>
            <span>Directeur des Études</span>
        </div>
    </div>

    <nav class="de-menu">
        <p>Vue d'ensemble</p>
        <a class="<?= $active_page === 'dashboard' ? 'active' : '' ?>" href="dashboard_de.php">
            <i class="fa-solid fa-table-cells-large"></i> Tableau de bord
        </a>
        <p>Gestion des mémoires</p>
        <a class="<?= $active_page === 'publications' ? 'active' : '' ?>" href="publier_memoire.php">
            <i class="fa-solid fa-upload"></i> Publier un mémoire
        </a>
        <a class="<?= $active_page === 'anciens' ? 'active' : '' ?>" href="dashboard_de.php#publications">
            <i class="fa-solid fa-book-open-reader"></i> Anciens mémoires
        </a>
        <a class="<?= $active_page === 'notifications' ? 'active' : '' ?>" href="notifications.php">
            <i class="fa-solid fa-bell"></i> Notifications
        </a>
        <p>Utilisateurs</p>
        <a class="<?= $active_page === 'professeurs' ? 'active' : '' ?>" href="professeurs_de.php">
            <i class="fa-solid fa-user-plus"></i> Ajouter professeur
        </a>
        <a class="<?= $active_page === 'logout' ? 'active' : '' ?>" href="../../../public/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
        <a class="<?= $active_page === 'etudiants' ? 'active' : '' ?>" href="etudiants_de.php">
            <i class="fa-solid fa-users"></i> Étudiants
        </a>
    </nav>
</aside>

