<?php

// =====================================================
// DLGC Assistance
// Liste des demandes actives
// =====================================================

require __DIR__ . '/../lib/bootstrap.php';


// -----------------------------------------------------
// يجب أن يكون الموظف مسجل الدخول
// -----------------------------------------------------

require_admin();


// -----------------------------------------------------
// الاتصال بقاعدة البيانات
// -----------------------------------------------------

$pdo = db();


// -----------------------------------------------------
// البحث
// -----------------------------------------------------

$q = trim(
    (string) ($_GET['q'] ?? '')
);


// =====================================================
// جلب الطلبات النشطة
//
// Nouveau + En cours فقط
//
// assigned_name:
// اسم الموظف الذي أخذ الطلب
// =====================================================

$sql = "
    SELECT
        r.*,
        a.display_name AS assigned_name

    FROM assistance_requests r

    LEFT JOIN admins a
        ON a.id = r.assigned_admin_id

    WHERE r.status IN ('nouveau', 'en_cours')
";


$params = [];


// -----------------------------------------------------
// إضافة البحث إذا كتب المستخدم شيئًا
// -----------------------------------------------------

if ($q !== '') {

    $sql .= "
        AND (
            r.ticket_code LIKE :q
            OR r.client_code LIKE :q
            OR r.company_name LIKE :q
            OR r.phone LIKE :q
            OR r.subject LIKE :q
            OR r.description LIKE :q
        )
    ";

    $params[':q'] =
        '%' . $q . '%';
}


// -----------------------------------------------------
// الترتيب:
//
// 1. Nouveau أولًا
// 2. En cours بعده
//
// داخل كل مجموعة:
// الأحدث أولًا
// -----------------------------------------------------

$sql .= "
    ORDER BY
        CASE
            WHEN r.status = 'nouveau' THEN 0
            ELSE 1
        END,
        r.created_at DESC

    LIMIT 300
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$requests = $stmt->fetchAll();


// =====================================================
// الإحصائيات
// =====================================================

$counts = $pdo->query(
    "SELECT

        COALESCE(
            SUM(status = 'nouveau'),
            0
        ) AS nouveaux,

        COALESCE(
            SUM(status = 'en_cours'),
            0
        ) AS en_cours,

        COALESCE(
            SUM(status = 'resolu'),
            0
        ) AS resolus

     FROM assistance_requests"
)->fetch();

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
        Demandes actives - DLGC Assistance
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
             TITRE
             ========================================= -->

        <h1>
            Demandes actives
        </h1>


        <p class="lead">

            Les demandes résolues ne restent pas ici :
            elles sont déplacées automatiquement
            dans l’Historique.

        </p>



        <!-- =========================================
             STATISTIQUES + RECHERCHE
             ========================================= -->

        <div class="toolbar">


            <div class="small">

                <strong>
                    <?= (int) ($counts['nouveaux'] ?? 0) ?>
                </strong>

                nouveau(x)

                •

                <strong>
                    <?= (int) ($counts['en_cours'] ?? 0) ?>
                </strong>

                en cours

                •

                <strong>
                    <?= (int) ($counts['resolus'] ?? 0) ?>
                </strong>

                résolu(s)

            </div>



            <form
                class="search-form"
                method="get"
            >


                <input
                    name="q"
                    value="<?= e($q) ?>"
                    placeholder="Rechercher : n° demande, code client, société, téléphone, sujet…"
                >


                <button
                    class="btn btn-primary"
                    type="submit"
                >

                    Rechercher

                </button>


                <?php if ($q !== ''): ?>

                    <a
                        class="btn btn-light"
                        href="index.php"
                    >

                        Effacer

                    </a>

                <?php endif; ?>


            </form>


        </div>



        <!-- =========================================
             TABLEAU DES DEMANDES
             ========================================= -->

        <div class="table-wrap">


            <table>


                <thead>

                    <tr>

                        <th>
                            N° demande
                        </th>

                        <th>
                            Code client
                        </th>

                        <th>
                            Société
                        </th>

                        <th>
                            Téléphone
                        </th>

                        <th>
                            Sujet
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Statut
                        </th>

                        <th>
                            Prise en charge par
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>



                <tbody>


                <?php if (!$requests): ?>


                    <tr>

                        <td
                            colspan="9"
                            class="empty"
                        >

                            Aucune demande active trouvée.

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($requests as $request): ?>


                        <?php

                        // -----------------------------------------
                        // هل الموظف الحالي هو صاحب هذا الطلب؟
                        // -----------------------------------------

                        $isMine =
                            !empty($request['assigned_admin_id'])
                            &&
                            (int) $request['assigned_admin_id']
                            === current_admin_id();

                        ?>


                        <tr>


                            <!-- =============================
                                 N° DEMANDE
                                 ============================= -->

                            <td>

                                <strong>
                                    <?= e($request['ticket_code']) ?>
                                </strong>

                            </td>



                            <!-- =============================
                                 CODE CLIENT
                                 ============================= -->

                            <td>

                                <?= e(
                                    $request['client_code']
                                    ?: '—'
                                ) ?>

                            </td>



                            <!-- =============================
                                 SOCIÉTÉ
                                 ============================= -->

                            <td>

                                <?= e($request['company_name']) ?>

                            </td>



                            <!-- =============================
                                 TÉLÉPHONE
                                 ============================= -->

                            <td>

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

                            </td>



                            <!-- =============================
                                 SUJET
                                 ============================= -->

                            <td>

                                <?= e($request['subject']) ?>

                            </td>



                            <!-- =============================
                                 DATE
                                 ============================= -->

                            <td>

                                <?= e(
                                    date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $request['created_at']
                                        )
                                    )
                                ) ?>

                            </td>



                            <!-- =============================
                                 STATUT
                                 ============================= -->

                            <td>

                                <span
                                    class="status <?= e(
                                        status_class(
                                            $request['status']
                                        )
                                    ) ?>"
                                >

                                    <?= e(
                                        status_label(
                                            $request['status']
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- =============================
                                 PRISE EN CHARGE PAR
                                 ============================= -->

                            <td>


                                <?php if ($request['status'] === 'nouveau'): ?>


                                    <span class="small">
                                        —
                                    </span>


                                <?php elseif ($request['assigned_name']): ?>


                                    <strong>

                                        <?= e(
                                            $request['assigned_name']
                                        ) ?>

                                    </strong>


                                    <?php if ($isMine): ?>

                                        <div class="small">

                                            (Vous)

                                        </div>

                                    <?php endif; ?>


                                <?php else: ?>


                                    <!--
                                        حالة غير طبيعية:
                                        الطلب En cours
                                        لكن لا يوجد موظف مرتبط به.
                                    -->

                                    <span class="small">

                                        Non identifié

                                    </span>


                                <?php endif; ?>


                            </td>



                            <!-- =============================
                                 ACTION
                                 ============================= -->

                            <td>

                                <a
                                    class="btn btn-light"
                                    href="ticket.php?id=<?= (int) $request['id'] ?>"
                                >

                                    Ouvrir

                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </section>


</main>


</body>

</html>