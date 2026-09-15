<?php

// =====================================================
// DLGC Assistance
// Détail d'une demande d'assistance
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// يجب أن يكون المستخدم مسجل الدخول
// -----------------------------------------------------

require_admin();


// -----------------------------------------------------
// قراءة ID الطلب من الرابط
// مثال:
// ticket.php?id=15
// -----------------------------------------------------

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


// -----------------------------------------------------
// التحقق من ID
// -----------------------------------------------------

if (!$id) {

    http_response_code(400);

    exit(
        'Identifiant de demande invalide.'
    );
}


// -----------------------------------------------------
// جلب الطلب
//
// assigned_name:
// اسم الموظف الذي أخذ الطلب
//
// resolved_name:
// اسم الموظف الذي أنهى الطلب
// -----------------------------------------------------

$stmt = db()->prepare(
    'SELECT
        r.*,
        aa.display_name AS assigned_name,
        ra.display_name AS resolved_name
     FROM assistance_requests r
     LEFT JOIN admins aa
        ON aa.id = r.assigned_admin_id
     LEFT JOIN admins ra
        ON ra.id = r.resolved_by_admin_id
     WHERE r.id = :id
     LIMIT 1'
);


$stmt->execute([
    ':id' => $id,
]);


$request = $stmt->fetch();


// -----------------------------------------------------
// الطلب غير موجود
// -----------------------------------------------------

if (!$request) {

    http_response_code(404);

    exit(
        'Demande introuvable.'
    );
}


// -----------------------------------------------------
// هل الموظف الحالي هو من أخذ الطلب؟
// -----------------------------------------------------

$isAssignedToCurrentAdmin =
    !empty($request['assigned_admin_id'])
    &&
    (int) $request['assigned_admin_id'] === current_admin_id();

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
        <?= e($request['ticket_code']) ?>
        - DLGC Assistance
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


        <!-- =========================================
             EN-TÊTE DE LA DEMANDE
             ========================================= -->

        <div class="toolbar">


            <div>

                <h1 style="margin-bottom:6px;">

                    <?= e($request['ticket_code']) ?>

                </h1>


                <span
                    class="status <?= e(status_class($request['status'])) ?>"
                >

                    <?= e(status_label($request['status'])) ?>

                </span>

            </div>



            <a
                class="btn btn-light"
                href="<?= $request['status'] === 'resolu'
                    ? 'history.php'
                    : 'index.php' ?>"
            >

                ← Retour

            </a>


        </div>



        <!-- =========================================
             INFORMATION IMPORTANTE
             QUI TRAITE LA DEMANDE ?
             ========================================= -->


        <?php if ($request['status'] === 'nouveau'): ?>

            <div
                class="alert"
                style="
                    margin-top:20px;
                    background:#eef6ff;
                    border:1px solid #b8d8f5;
                "
            >

                <strong>
                    Demande non prise en charge.
                </strong>

                <br>

                Aucun employé n’a encore commencé
                le traitement de cette demande.

            </div>

        <?php endif; ?>



        <?php if ($request['status'] === 'en_cours'): ?>


            <?php if ($isAssignedToCurrentAdmin): ?>

                <div
                    class="alert alert-success"
                    style="margin-top:20px;"
                >

                    <strong>
                        Vous avez pris en charge cette demande.
                    </strong>

                    <br>

                    Elle est actuellement en cours de traitement.

                </div>


            <?php else: ?>

                <div
                    class="alert"
                    style="
                        margin-top:20px;
                        background:#fff7df;
                        border:1px solid #ecd48a;
                    "
                >

                    <strong>
                        Attention :
                        cette demande est déjà prise en charge.
                    </strong>

                    <br>

                    Employé :

                    <strong>
                        <?= e(
                            $request['assigned_name']
                            ?: 'Employé non identifié'
                        ) ?>
                    </strong>

                    <br>

                    Vérifiez la situation avant de contacter
                    le client afin d’éviter deux appels
                    pour la même demande.

                </div>

            <?php endif; ?>


        <?php endif; ?>



        <?php if ($request['status'] === 'resolu'): ?>

            <div
                class="alert alert-success"
                style="margin-top:20px;"
            >

                <strong>
                    Cette demande est terminée.
                </strong>

                <?php if ($request['resolved_name']): ?>

                    <br>

                    Résolue par :

                    <strong>
                        <?= e($request['resolved_name']) ?>
                    </strong>

                <?php endif; ?>

            </div>

        <?php endif; ?>



        <!-- =========================================
             INFORMATIONS DE LA DEMANDE
             ========================================= -->

        <div class="meta-grid">


            <div class="meta-item">

                <strong>
                    Code client
                </strong>

                <?= e(
                    $request['client_code']
                    ?: 'Non renseigné'
                ) ?>

            </div>



            <div class="meta-item">

                <strong>
                    Société
                </strong>

                <?= e($request['company_name']) ?>

            </div>



            <div class="meta-item">

                <strong>
                    Téléphone
                </strong>

                <a
                    href="tel:<?= e(
                        preg_replace(
                            '/[^0-9+]/',
                            '',
                            $request['phone']
                        )
                    ) ?>"
                >

                    <?= e($request['phone']) ?>

                </a>

            </div>



            <div class="meta-item">

                <strong>
                    Sujet
                </strong>

                <?= e($request['subject']) ?>

            </div>



            <div class="meta-item">

                <strong>
                    Créée le
                </strong>

                <?= e(
                    date(
                        'd/m/Y H:i',
                        strtotime($request['created_at'])
                    )
                ) ?>

            </div>



            <!-- =====================================
                 PRISE EN CHARGE
                 ===================================== -->

            <div class="meta-item">

                <strong>
                    Prise en charge le
                </strong>

                <?php if ($request['started_at']): ?>

                    <?= e(
                        date(
                            'd/m/Y H:i',
                            strtotime($request['started_at'])
                        )
                    ) ?>

                <?php else: ?>

                    Pas encore

                <?php endif; ?>

            </div>



            <div class="meta-item">

                <strong>
                    Prise en charge par
                </strong>

                <?php if ($request['assigned_name']): ?>

                    <?= e($request['assigned_name']) ?>

                    <?php if ($isAssignedToCurrentAdmin): ?>

                        <strong>
                            (Vous)
                        </strong>

                    <?php endif; ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>



            <!-- =====================================
                 INFORMATIONS DE RÉSOLUTION
                 ===================================== -->

            <?php if ($request['status'] === 'resolu'): ?>


                <div class="meta-item">

                    <strong>
                        Résolue le
                    </strong>

                    <?php if ($request['resolved_at']): ?>

                        <?= e(
                            date(
                                'd/m/Y H:i',
                                strtotime($request['resolved_at'])
                            )
                        ) ?>

                    <?php else: ?>

                        —

                    <?php endif; ?>

                </div>



                <div class="meta-item">

                    <strong>
                        Résolue par
                    </strong>

                    <?= e(
                        $request['resolved_name']
                        ?: '—'
                    ) ?>

                </div>


            <?php endif; ?>


        </div>



        <!-- =========================================
             DESCRIPTION
             ========================================= -->

        <h3
            style="
                margin-top:22px;
                margin-bottom:8px;
            "
        >

            Description du problème

        </h3>


        <div class="description-box">

            <?= e($request['description']) ?>

        </div>



        <!-- =========================================
             ACTIONS
             ========================================= -->

        <?php if ($request['status'] !== 'resolu'): ?>


            <div class="actions">


                <!-- =================================
                     NOUVEAU
                     ================================= -->

                <?php if ($request['status'] === 'nouveau'): ?>


                    <form
                        action="action.php"
                        method="post"
                    >


                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrf_token()) ?>"
                        >


                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $request['id'] ?>"
                        >


                        <input
                            type="hidden"
                            name="action"
                            value="start"
                        >


                        <button
                            class="btn btn-primary"
                            type="submit"
                        >

                            Prendre en charge

                        </button>


                    </form>


                <?php endif; ?>



                <!-- =================================
                     EN COURS
                     ================================= -->

                <?php if ($request['status'] === 'en_cours'): ?>


                    <form
                        action="action.php"
                        method="post"
                        onsubmit="return confirm(
                            'Confirmer que cette demande est terminée ? Elle sera déplacée vers l’historique.'
                        );"
                    >


                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrf_token()) ?>"
                        >


                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $request['id'] ?>"
                        >


                        <input
                            type="hidden"
                            name="action"
                            value="resolve"
                        >


                        <button
                            class="btn btn-green"
                            type="submit"
                        >

                            ✓ Marquer comme résolu

                        </button>


                    </form>


                <?php endif; ?>


            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>