<?php

require __DIR__ . '/../lib/bootstrap.php';

// هذه الصفحة للـ Super Admin فقط
require_super_admin();

$pdo = db();


// ==========================================
// جلب جميع حسابات الموظفين
// ==========================================

$users = $pdo->query(
    "SELECT
        id,
        username,
        display_name,
        role,
        is_active,
        created_at
     FROM admins
     ORDER BY
        is_active DESC,
        display_name ASC"
)->fetchAll();


$message = trim(
    (string) ($_GET['message'] ?? '')
);

$error = trim(
    (string) ($_GET['error'] ?? '')
);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Utilisateurs - DLGC Assistance
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/assistance.css"
    >

</head>


<body>

<main class="admin-shell">


    <?php require __DIR__ . '/_nav.php'; ?>



    <!-- =====================================
         AJOUTER UN EMPLOYÉ
         ===================================== -->

    <section class="card">

        <h1>
            Gestion des utilisateurs
        </h1>


        <p class="lead">

            Créez et gérez les comptes
            des employés DLGC autorisés
            à traiter les demandes d’assistance.

        </p>



        <?php if ($message !== ''): ?>

            <div class="alert alert-success">
                <?= e($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ''): ?>

            <div class="alert alert-error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>



        <h2>
            Ajouter un utilisateur
        </h2>


        <form
            method="post"
            action="user_action.php"
        >


            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >


            <input
                type="hidden"
                name="action"
                value="create"
            >



            <div class="form-grid">


                <div>

                    <label for="display_name">
                        Nom affiché *
                    </label>

                    <input
                        id="display_name"
                        name="display_name"
                        maxlength="100"
                        required
                    >

                </div>



                <div>

                    <label for="username">
                        Nom d’utilisateur *
                    </label>

                    <input
                        id="username"
                        name="username"
                        maxlength="60"
                        required
                        autocomplete="off"
                    >

                </div>



                <div>

                    <label for="password">
                        Mot de passe *
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        minlength="10"
                        required
                        autocomplete="new-password"
                    >

                </div>



                <div>

                    <label for="password_confirm">
                        Confirmer le mot de passe *
                    </label>

                    <input
                        id="password_confirm"
                        name="password_confirm"
                        type="password"
                        minlength="10"
                        required
                        autocomplete="new-password"
                    >

                </div>



                <div>

                    <label for="role">
                        Rôle *
                    </label>

                    <select
                        id="role"
                        name="role"
                        required
                    >

                        <option value="employee">
                            Employé
                        </option>

                        <option value="super_admin">
                            Super Admin
                        </option>

                    </select>

                </div>


            </div>



            <div class="actions">

                <button
                    class="btn btn-green"
                    type="submit"
                >

                    + Créer le compte

                </button>

            </div>


        </form>


    </section>



    <!-- =====================================
         LISTE DES COMPTES
         ===================================== -->

    <section class="card">

        <h2>
            Comptes existants
        </h2>


        <div class="table-wrap">

            <table>


                <thead>

                    <tr>

                        <th>
                            Nom
                        </th>

                        <th>
                            Utilisateur
                        </th>

                        <th>
                            Rôle
                        </th>

                        <th>
                            Statut
                        </th>

                        <th>
                            Créé le
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>



                <tbody>


                <?php foreach ($users as $user): ?>


                    <tr>


                        <td>

                            <strong>
                                <?= e($user['display_name']) ?>
                            </strong>

                        </td>



                        <td>
                            <?= e($user['username']) ?>
                        </td>



                        <td>

                            <?php if ($user['role'] === 'super_admin'): ?>

                                <strong>
                                    Super Admin
                                </strong>

                            <?php else: ?>

                                Employé

                            <?php endif; ?>

                        </td>



                        <td>

                            <?php if ((int) $user['is_active'] === 1): ?>

                                <span class="status status-resolved">
                                    Actif
                                </span>

                            <?php else: ?>

                                <span class="status">
                                    Désactivé
                                </span>

                            <?php endif; ?>

                        </td>



                        <td>

                            <?= e(
                                date(
                                    'd/m/Y H:i',
                                    strtotime($user['created_at'])
                                )
                            ) ?>

                        </td>



                        <td>

                            <!-- =====================================
                                 زر إعادة تعيين كلمة المرور
                                 ===================================== -->

                            <a
                                class="btn btn-primary"
                                href="reset_password.php?id=<?= (int) $user['id'] ?>"
                            >
                                Mot de passe
                            </a>


                            <!-- =====================================
                                 الحساب الحالي
                                 ===================================== -->

                            <?php if ((int) $user['id'] === current_admin_id()): ?>

                                <span class="small">
                                    Votre compte
                                </span>


                            <?php else: ?>


                                <!-- =================================
                                     Activer / Désactiver
                                     ================================= -->

                                <form
                                    method="post"
                                    action="user_action.php"
                                    style="display:inline-block;"
                                >


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e(csrf_token()) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="toggle"
                                    >


                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $user['id'] ?>"
                                    >



                                    <?php if ((int) $user['is_active'] === 1): ?>


                                        <button
                                            class="btn btn-danger"
                                            type="submit"
                                            onclick="return confirm('Désactiver ce compte ?');"
                                        >

                                            Désactiver

                                        </button>


                                    <?php else: ?>


                                        <button
                                            class="btn btn-green"
                                            type="submit"
                                        >

                                            Activer

                                        </button>


                                    <?php endif; ?>


                                </form>


                            <?php endif; ?>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>

        </div>


    </section>


</main>


</body>

</html>