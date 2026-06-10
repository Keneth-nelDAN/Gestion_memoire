<!-- SIDEBAR -->
        <aside class="sidebar" id="appSidebar">
            <div class="logo-block">
                <span class="logo-icon">M</span>
                <div>
                    <h2 class="logo-title">GénieMémoire</h2>
                    <p class="logo-subtitle">UATM GASA</p>
                </div>
            </div>

            <!-- Profile Info block -->
            <div class="profile-card">
                <h3><?php echo $prenom . ' ' . $nom; ?></h3>
                <p><i class="fas fa-user-tie me-1"></i> Professeur</p>
            </div>

            <!-- NAVIGATION (Liens Relatifs Universels) -->
            <ul class="nav-menu">
                <li>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_professeur.php' ? 'active' : ''; ?>" href="dashboard_professeur.php">
                        <i class="fas fa-chart-pie"></i> Tableau de bord
                    </a>
                </li>
                <li>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) == 'memoire_jury.php' ? 'active' : ''; ?>" href="memoire_jury.php">
                        <i class="fas fa-book-open"></i> Mémoires en jury
                    </a>
                </li>
                <li>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) == 'validation_memoire.php' ? 'active' : ''; ?>" href="validation_memoire.php">
                        <i class="fas fa-check-circle"></i> Validations & Notes
                    </a>
                </li>
                <li>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) == 'observations.php' ? 'active' : ''; ?>" href="observations.php">
                        <i class="fas fa-comments"></i> Observations
                    </a>
                </li>
                <li>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>" href="notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
            </ul>

            <!-- Bouton Déconnexion Relatif -->
            <a href="../../../public/logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </aside>