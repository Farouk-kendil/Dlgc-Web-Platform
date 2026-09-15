<nav class="admin-nav">

    <div>
        <strong>
            DLGC • Assistance
        </strong>

        <br>

        <span class="small" style="color:#dcecff;">

            Connecté :
            <?= e($_SESSION['admin_name'] ?? 'Admin') ?>

            <?php if (is_super_admin()): ?>
                — Super Admin
            <?php endif; ?>

        </span>
    </div>


    <div class="admin-links">

        <a href="index.php">
            Demandes actives
        </a>

        <a href="history.php">
            Historique
        </a>


        <?php if (is_super_admin()): ?>

            <a href="users.php">
                Utilisateurs
            </a>

        <?php endif; ?>


        <a href="logout.php">
            Déconnexion
        </a>

    </div>

</nav>