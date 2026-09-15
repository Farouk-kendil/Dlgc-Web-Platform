<?php

// =====================================================
// DLGC Assistance - Connexion Administration
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// إذا كان الموظف مسجل الدخول بالفعل
// نرسله مباشرة إلى لوحة الإدارة
// -----------------------------------------------------

if (is_admin_logged_in()) {
    redirect('index.php');
}


// -----------------------------------------------------
// الاتصال بقاعدة البيانات
// -----------------------------------------------------

$pdo = db();


// -----------------------------------------------------
// معرفة هل يوجد أي Admin في النظام
// إذا لم يوجد، نظهر رابط إنشاء أول Admin
// -----------------------------------------------------

$adminCount = (int) $pdo
    ->query('SELECT COUNT(*) FROM admins')
    ->fetchColumn();

$error = null;


// =====================================================
// معالجة تسجيل الدخول
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // حماية CSRF
    verify_csrf();


    // ---------------------------------------------
    // قراءة البيانات القادمة من النموذج
    // ---------------------------------------------

    $username = trim(
        (string) ($_POST['username'] ?? '')
    );

    $password = (string) ($_POST['password'] ?? '');


    // ---------------------------------------------
    // البحث عن المستخدم
    //
    // مهم:
    // نجلب role أيضًا حتى نعرف هل هو
    // Super Admin أم Employé
    // ---------------------------------------------

    $stmt = $pdo->prepare(
        'SELECT
            id,
            username,
            display_name,
            password_hash,
            role
         FROM admins
         WHERE username = :username
         AND is_active = 1
         LIMIT 1'
    );


    $stmt->execute([
        ':username' => $username,
    ]);


    $admin = $stmt->fetch();


    // ---------------------------------------------
    // التحقق من كلمة المرور
    // ---------------------------------------------

    if (
        $admin &&
        password_verify(
            $password,
            $admin['password_hash']
        )
    ) {

        // حماية الجلسة بعد تسجيل الدخول
        session_regenerate_id(true);


        // -----------------------------------------
        // تخزين معلومات الموظف في Session
        // -----------------------------------------

        $_SESSION['admin_id'] =
            (int) $admin['id'];

        $_SESSION['admin_name'] =
            (string) $admin['display_name'];

        $_SESSION['admin_role'] =
            (string) ($admin['role'] ?? 'employee');


        // -----------------------------------------
        // الدخول إلى لوحة الطلبات
        // -----------------------------------------

        redirect('index.php');
    }


    // ---------------------------------------------
    // بيانات تسجيل الدخول غير صحيحة
    // ---------------------------------------------

    $error =
        'Nom d’utilisateur ou mot de passe incorrect.';
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
        Administration - DLGC Assistance
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/assistance.css"
    >

</head>


<body>

<main class="page-shell">


    <!-- ==========================================
         رأس صفحة الإدارة
         ========================================== -->

    <div class="topbar">

        <div class="brand">
            DLGC • Administration assistance
        </div>

    </div>



    <!-- ==========================================
         نموذج تسجيل الدخول
         ========================================== -->

    <section class="card">

        <h1>
            Connexion
        </h1>


        <p class="lead">
            Accès réservé à l’équipe DLGC.
        </p>



        <!-- ---------------------------------------
             رسالة نجاح إنشاء أول Admin
             --------------------------------------- -->

        <?php if (isset($_GET['created'])): ?>

            <div class="alert alert-success">

                Compte administrateur créé.
                Vous pouvez maintenant vous connecter.

            </div>

        <?php endif; ?>



        <!-- ---------------------------------------
             رسالة خطأ تسجيل الدخول
             --------------------------------------- -->

        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>



        <!-- ---------------------------------------
             لا يوجد أي Admin بعد
             --------------------------------------- -->

        <?php if ($adminCount === 0): ?>

            <div class="alert alert-error">

                Aucun administrateur n’existe encore.

                <a href="setup_admin.php">

                    <strong>
                        Créer le premier administrateur
                    </strong>

                </a>.

            </div>

        <?php endif; ?>



        <!-- ======================================
             Formulaire de connexion
             ====================================== -->

        <form method="post">


            <!-- CSRF -->

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >


            <div class="form-grid">


                <!-- Nom d'utilisateur -->

                <div>

                    <label for="username">
                        Nom d’utilisateur
                    </label>

                    <input
                        id="username"
                        name="username"
                        type="text"
                        maxlength="60"
                        required
                        autofocus
                        autocomplete="username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                    >

                </div>



                <!-- Mot de passe -->

                <div>

                    <label for="password">
                        Mot de passe
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                    >

                </div>


            </div>



            <div class="actions">

                <button
                    class="btn btn-primary"
                    type="submit"
                >

                    Se connecter

                </button>

            </div>


        </form>


    </section>


</main>

</body>

</html>