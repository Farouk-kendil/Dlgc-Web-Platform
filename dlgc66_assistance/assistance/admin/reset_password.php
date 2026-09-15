<?php

// =====================================================
// DLGC Assistance
// Réinitialisation du mot de passe d'un utilisateur
// Réservé au Super Admin
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// هذه الصفحة خاصة بالـ Super Admin فقط
// -----------------------------------------------------

require_super_admin();


// -----------------------------------------------------
// الاتصال بقاعدة البيانات
// -----------------------------------------------------

$pdo = db();


// -----------------------------------------------------
// قراءة ID المستخدم من الرابط
// مثال:
// reset_password.php?id=3
// -----------------------------------------------------

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


// -----------------------------------------------------
// التحقق من أن ID صحيح
// -----------------------------------------------------

if (!$id) {

    http_response_code(400);

    exit(
        'Utilisateur invalide.'
    );
}


// -----------------------------------------------------
// جلب معلومات المستخدم من قاعدة البيانات
// -----------------------------------------------------

$stmt = $pdo->prepare(
    'SELECT
        id,
        username,
        display_name,
        role,
        is_active
     FROM admins
     WHERE id = :id
     LIMIT 1'
);


$stmt->execute([
    ':id' => $id,
]);


$user = $stmt->fetch();


// -----------------------------------------------------
// إذا لم نجد المستخدم
// -----------------------------------------------------

if (!$user) {

    http_response_code(404);

    exit(
        'Utilisateur introuvable.'
    );
}


// -----------------------------------------------------
// قراءة رسالة الخطأ إن وجدت
// سنستعملها في الخطوة القادمة
// -----------------------------------------------------

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
        Réinitialiser le mot de passe - DLGC Assistance
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/assistance.css"
    >

</head>


<body>

<main class="admin-shell">


    <?php require __DIR__ . '/_nav.php'; ?>



    <section class="card">


        <h1>
            Réinitialiser le mot de passe
        </h1>


        <p class="lead">

            Vous allez définir un nouveau mot de passe pour :

            <strong>
                <?= e($user['display_name']) ?>
            </strong>

        </p>


        <p class="small">

            Nom d’utilisateur :

            <strong>
                <?= e($user['username']) ?>
            </strong>

        </p>



        <!-- =====================================
             Message d'erreur
             ===================================== -->

        <?php if ($error !== ''): ?>

            <div class="alert alert-error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>



        <!-- =====================================
             Formulaire
             ===================================== -->

        <form
            method="post"
            action="user_action.php"
        >


            <!-- Protection CSRF -->

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >


            <!-- نوع العملية -->

            <input
                type="hidden"
                name="action"
                value="reset_password"
            >


            <!-- ID الموظف -->

            <input
                type="hidden"
                name="id"
                value="<?= (int) $user['id'] ?>"
            >



            <div class="form-grid">


                <!-- Nouveau mot de passe -->

                <div>

                    <label for="password">
                        Nouveau mot de passe *
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



                <!-- Confirmation -->

                <div>

                    <label for="password_confirm">
                        Confirmer le nouveau mot de passe *
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


            </div>



            <div class="actions">


                <button
                    class="btn btn-green"
                    type="submit"
                >

                    Enregistrer le nouveau mot de passe

                </button>


                <a
                    class="btn"
                    href="users.php"
                >

                    Annuler

                </a>


            </div>


        </form>


    </section>


</main>


</body>

</html>