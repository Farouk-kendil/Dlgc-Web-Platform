<?php

// =====================================================
// DLGC Assistance
// Actions de gestion des utilisateurs
// Réservé au Super Admin
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// هذه الصفحة للـ Super Admin فقط
// -----------------------------------------------------

require_super_admin();


// -----------------------------------------------------
// نقبل POST فقط
// -----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users.php');
}


// -----------------------------------------------------
// حماية CSRF
// -----------------------------------------------------

verify_csrf();


// -----------------------------------------------------
// الاتصال بقاعدة البيانات
// -----------------------------------------------------

$pdo = db();


// -----------------------------------------------------
// معرفة نوع العملية المطلوبة
//
// create         = إنشاء حساب
// reset_password = تغيير كلمة المرور
// toggle         = تفعيل / تعطيل حساب
// -----------------------------------------------------

$action = trim(
    (string) ($_POST['action'] ?? '')
);


// =====================================================
// 1. إنشاء مستخدم جديد
// =====================================================

if ($action === 'create') {

    // ---------------------------------------------
    // قراءة البيانات
    // ---------------------------------------------

    $displayName = trim(
        (string) ($_POST['display_name'] ?? '')
    );

    $username = trim(
        (string) ($_POST['username'] ?? '')
    );

    $password =
        (string) ($_POST['password'] ?? '');

    $passwordConfirm =
        (string) ($_POST['password_confirm'] ?? '');

    $role = trim(
        (string) ($_POST['role'] ?? 'employee')
    );


    // =============================================
    // التحقق من الاسم الظاهر
    // =============================================

    if (
        $displayName === '' ||
        mb_strlen($displayName) > 100
    ) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Nom affiché invalide.'
            )
        );
    }


    // =============================================
    // التحقق من اسم المستخدم
    // =============================================

    if (
        !preg_match(
            '/^[A-Za-z0-9_.-]{3,60}$/',
            $username
        )
    ) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Le nom d’utilisateur doit contenir 3 à 60 caractères : lettres, chiffres, point, tiret ou underscore.'
            )
        );
    }


    // =============================================
    // التحقق من كلمة المرور
    // =============================================

    if (strlen($password) < 10) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Le mot de passe doit contenir au moins 10 caractères.'
            )
        );
    }


    // =============================================
    // التحقق من تطابق كلمتي المرور
    // =============================================

    if ($password !== $passwordConfirm) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Les deux mots de passe ne correspondent pas.'
            )
        );
    }


    // =============================================
    // التحقق من الصلاحية
    // =============================================

    $allowedRoles = [
        'employee',
        'super_admin',
    ];

    if (
        !in_array(
            $role,
            $allowedRoles,
            true
        )
    ) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Rôle invalide.'
            )
        );
    }


    // =============================================
    // التأكد أن username غير موجود مسبقًا
    // =============================================

    $check = $pdo->prepare(
        'SELECT COUNT(*)
         FROM admins
         WHERE username = :username'
    );

    $check->execute([
        ':username' => $username,
    ]);

    $usernameExists =
        (int) $check->fetchColumn();


    if ($usernameExists > 0) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Ce nom d’utilisateur existe déjà.'
            )
        );
    }


    // =============================================
    // إنشاء الحساب
    // =============================================

    $stmt = $pdo->prepare(
        'INSERT INTO admins
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
            :role,
            1
        )'
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

        ':role' =>
            $role,
    ]);


    redirect(
        'users.php?message=' .
        urlencode(
            'Compte créé avec succès.'
        )
    );
}


// =====================================================
// 2. إعادة تعيين كلمة المرور
// =====================================================

if ($action === 'reset_password') {

    // ---------------------------------------------
    // قراءة ID المستخدم
    // ---------------------------------------------

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


    if (!$id) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Utilisateur invalide.'
            )
        );
    }


    // ---------------------------------------------
    // قراءة كلمة المرور الجديدة
    // ---------------------------------------------

    $password =
        (string) ($_POST['password'] ?? '');

    $passwordConfirm =
        (string) ($_POST['password_confirm'] ?? '');


    // =============================================
    // كلمة المرور يجب أن تكون 10 أحرف على الأقل
    // =============================================

    if (strlen($password) < 10) {

        redirect(
            'reset_password.php?id=' .
            $id .
            '&error=' .
            urlencode(
                'Le mot de passe doit contenir au moins 10 caractères.'
            )
        );
    }


    // =============================================
    // التحقق من تطابق كلمتي المرور
    // =============================================

    if ($password !== $passwordConfirm) {

        redirect(
            'reset_password.php?id=' .
            $id .
            '&error=' .
            urlencode(
                'Les deux mots de passe ne correspondent pas.'
            )
        );
    }


    // =============================================
    // التأكد أن المستخدم موجود
    // =============================================

    $check = $pdo->prepare(
        'SELECT
            id,
            username,
            display_name
         FROM admins
         WHERE id = :id
         LIMIT 1'
    );


    $check->execute([
        ':id' => $id,
    ]);


    $user = $check->fetch();


    if (!$user) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Utilisateur introuvable.'
            )
        );
    }


    // =============================================
    // إنشاء Hash جديد لكلمة المرور
    // =============================================

    $newPasswordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    // =============================================
    // تحديث كلمة المرور في قاعدة البيانات
    // =============================================

    $update = $pdo->prepare(
        'UPDATE admins
         SET password_hash = :password_hash
         WHERE id = :id'
    );


    $update->execute([

        ':password_hash' =>
            $newPasswordHash,

        ':id' =>
            $id,
    ]);


    // =============================================
    // العودة إلى صفحة المستخدمين
    // =============================================

    redirect(
        'users.php?message=' .
        urlencode(
            'Mot de passe réinitialisé avec succès.'
        )
    );
}


// =====================================================
// 3. تفعيل / تعطيل حساب
// =====================================================

if ($action === 'toggle') {

    // ---------------------------------------------
    // قراءة ID المستخدم
    // ---------------------------------------------

    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


    if (!$id) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Utilisateur invalide.'
            )
        );
    }


    // =============================================
    // Super Admin لا يستطيع تعطيل نفسه
    // =============================================

    if ($id === current_admin_id()) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Vous ne pouvez pas désactiver votre propre compte.'
            )
        );
    }


    // =============================================
    // جلب الحساب المطلوب
    // =============================================

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


    if (!$user) {

        redirect(
            'users.php?error=' .
            urlencode(
                'Utilisateur introuvable.'
            )
        );
    }


    // =============================================
    // إذا كان Actif نجعله Désactivé
    // وإذا كان Désactivé نجعله Actif
    // =============================================

    $newStatus =
        ((int) $user['is_active'] === 1)
            ? 0
            : 1;


    // =============================================
    // حماية:
    // لا نسمح بتعطيل آخر Super Admin نشط
    // =============================================

    if (
        $user['role'] === 'super_admin' &&
        $newStatus === 0
    ) {

        $activeSuperAdmins =
            (int) $pdo->query(
                "SELECT COUNT(*)
                 FROM admins
                 WHERE role = 'super_admin'
                 AND is_active = 1"
            )->fetchColumn();


        if ($activeSuperAdmins <= 1) {

            redirect(
                'users.php?error=' .
                urlencode(
                    'Impossible de désactiver le dernier Super Admin actif.'
                )
            );
        }
    }


    // =============================================
    // تحديث حالة الحساب
    // =============================================

    $update = $pdo->prepare(
        'UPDATE admins
         SET is_active = :is_active
         WHERE id = :id'
    );


    $update->execute([

        ':is_active' =>
            $newStatus,

        ':id' =>
            $id,
    ]);


    // =============================================
    // رسالة نجاح
    // =============================================

    if ($newStatus === 1) {

        $message =
            'Compte activé avec succès.';

    } else {

        $message =
            'Compte désactivé avec succès.';
    }


    redirect(
        'users.php?message=' .
        urlencode(
            $message
        )
    );
}


// =====================================================
// إذا وصلنا هنا فالعملية غير معروفة
// =====================================================

http_response_code(400);

exit(
    'Action inconnue.'
);