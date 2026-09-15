<?php

// =====================================================
// DLGC Assistance
// Création initiale du premier Super Admin
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// الاتصال بقاعدة البيانات
// -----------------------------------------------------

$pdo = db();


// -----------------------------------------------------
// حساب عدد المستخدمين الموجودين
// -----------------------------------------------------

$count = (int) $pdo
    ->query('SELECT COUNT(*) FROM admins')
    ->fetchColumn();

$errors = [];


// -----------------------------------------------------
// حماية مهمة:
// هذه الصفحة تعمل فقط إذا لم يوجد أي Admin
// -----------------------------------------------------

if ($count > 0) {

    http_response_code(403);

    exit(
        'Un compte administrateur existe déjà. '
        . 'Cette page de création initiale est maintenant désactivée.'
    );
}


// =====================================================
// معالجة إنشاء أول Super Admin
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // حماية CSRF
    verify_csrf();


    // -------------------------------------------------
    // قراءة البيانات
    // -------------------------------------------------

    $username = trim(
        (string) ($_POST['username'] ?? '')
    );

    $displayName = trim(
        (string) ($_POST['display_name'] ?? '')
    );

    $password =
        (string) ($_POST['password'] ?? '');

    $passwordConfirm =
        (string) ($_POST['password_confirm'] ?? '');


    // =================================================
    // التحقق من Nom d'utilisateur
    // =================================================

    if (
        !preg_match(
            '/^[A-Za-z0-9_.-]{3,60}$/',
            $username
        )
    ) {

        $errors[] =
            'Le nom d’utilisateur doit contenir '
            . '3 à 60 caractères : lettres, chiffres, '
            . 'point, tiret ou underscore.';
    }


    // =================================================
    // التحقق من الاسم الظاهر
    // =================================================

    if (
        $displayName === '' ||
        mb_strlen($displayName) > 100
    ) {

        $errors[] =
            'Le nom affiché est obligatoire.';
    }


    // =================================================
    // التحقق من كلمة المرور
    // =================================================

    if (strlen($password) < 10) {

        $errors[] =
            'Le mot de passe doit contenir '
            . 'au moins 10 caractères.';
    }


    // =================================================
    // التحقق من تطابق كلمتي المرور
    // =================================================

    if ($password !== $passwordConfirm) {

        $errors[] =
            'Les deux mots de passe '
            . 'ne correspondent pas.';
    }


    // =================================================
    // إذا لم توجد أخطاء:
    // إنشاء أول حساب بصفة Super Admin
    // =================================================

    if (!$errors) {

        $stmt = $pdo->prepare(
            "INSERT INTO admins
            (
                username,
                display_name,
                password_hash,
                role,
                is_active
            )
            VALUES
            (
                :username,
                :display_name,
                :password_hash,
                'super_admin',
                1
            )"
        );


        $stmt->execute([

            ':username' =>
                $username,

            ':display_name' =>
                $displayName,

            ':password_hash' =>
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
        ]);


        // بعد الإنشاء نذهب إلى صفحة تسجيل الدخول
        redirect('login.php?created=1');
    }
}

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
        Créer le Super Admin - DLGC Assistance
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/assistance.css"
    >

</head>


<body>


<main class="page-shell">


    <!-- ==========================================
         رأس الصفحة
         ========================================== -->

    <div class="topbar">

        <div class="brand">
            DLGC • Configuration initiale
        </div>

    </div>



    <!-- ==========================================
         إنشاء أول Super Admin
         ========================================== -->

    <section class="card">


        <h1>
            Créer le premier Super Admin
        </h1>


        <p class="lead">

            Ce compte sera le responsable principal
            de l’administration DLGC Assistance.

            Il pourra ensuite créer les comptes
            des autres employés.

        </p>



        <!-- ======================================
             عرض الأخطاء
             ====================================== -->

        <?php if ($errors): ?>

            <div class="alert alert-error">

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>



        <!-- ======================================
             Formulaire
             ====================================== -->

        <form method="post">


            <!-- Protection CSRF -->

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >


            <div class="form-grid">


                <!-- Nom affiché -->

                <div>

                    <label for="display_name">
                        Nom affiché *
                    </label>

                    <input
                        id="display_name"
                        name="display_name"
                        type="text"
                        required
                        maxlength="100"
                        autocomplete="name"
                        value="<?= e($_POST['display_name'] ?? '') ?>"
                    >

                </div>



                <!-- Nom utilisateur -->

                <div>

                    <label for="username">
                        Nom d’utilisateur *
                    </label>

                    <input
                        id="username"
                        name="username"
                        type="text"
                        required
                        maxlength="60"
                        autocomplete="username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                    >

                </div>



                <!-- Mot de passe -->

                <div>

                    <label for="password">
                        Mot de passe *
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        minlength="10"
                        autocomplete="new-password"
                    >

                </div>



                <!-- Confirmation -->

                <div>

                    <label for="password_confirm">
                        Confirmer le mot de passe *
                    </label>

                    <input
                        id="password_confirm"
                        name="password_confirm"
                        type="password"
                        required
                        minlength="10"
                        autocomplete="new-password"
                    >

                </div>


            </div>



            <div class="actions">

                <button
                    class="btn btn-green"
                    type="submit"
                >

                    Créer le Super Admin

                </button>

            </div>


        </form>


    </section>


</main>


</body>

</html>