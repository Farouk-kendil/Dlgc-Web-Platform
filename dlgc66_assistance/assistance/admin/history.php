<?php

// =====================================================
// DLGC Assistance
// Historique des demandes résolues
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
// جلب الطلبات المحلولة
//
// assigned_name:
// الموظف الذي أخذ الطلب
//
// resolved_name:
// الموظف الذي قام بحل الطلب
// =====================================================

$sql = "
    SELECT
        r.*,
        aa.display_name AS assigned_name,
        ra.display_name AS resolved_name

    FROM assistance_requests r

    LEFT JOIN admins aa
        ON aa.id = r.assigned_admin_id

    LEFT JOIN admins ra
        ON ra.id = r.resolved_by_admin_id

    WHERE r.status = 'resolu'
";


$params = [];


// -----------------------------------------------------
// البحث
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
// الأحدث حلًا يظهر أولًا
// -----------------------------------------------------

$sql .= "
    ORDER BY
        r.resolved_at DESC,
        r.created_at DESC

    LIMIT 500
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$requests = $stmt->fetchAll();

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
        Historique - DLGC Assistance
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
            Historique des demandes résolues
        </h1>


        <p class="lead">

            Ici restent toutes les demandes terminées.
            Elles ne sont pas supprimées.

        </p>



        <!-- =========================================
             RECHERCHE
             ========================================= -->

        <div class="toolbar">


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
                        href="history.php"
                    >

                        Effacer

                    </a>

                <?php endif; ?>


            </form>


        </div>



        <!-- =========================================
             TABLEAU
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
                            Statut
                        </th>

                        <th>
                            Prise en charge par
                        </th>

                        <th>
                            Résolue le
                        </th>

                        <th>
                            Résolue par
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
                            colspan="10"
                            class="empty"
                        >

                            Aucune demande résolue.

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($requests as $request): ?>


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

                                <?= e(
                                    $request['company_name']
                                ) ?>

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

                                <?= e(
                                    $request['subject']
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

                                <?php if ($request['assigned_name']): ?>

                                    <strong>
                                        <?= e(
                                            $request['assigned_name']
                                        ) ?>
                                    </strong>

                                <?php else: ?>

                                    <span class="small">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- =============================
                                 DATE DE RÉSOLUTION
                                 ============================= -->

                            <td>

                                <?php if ($request['resolved_at']): ?>

                                    <?= e(
                                        date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $request['resolved_at']
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>



                            <!-- =============================
                                 RÉSOLUE PAR
                                 ============================= -->

                            <td>

                                <?php if ($request['resolved_name']): ?>

                                    <strong>
                                        <?= e(
                                            $request['resolved_name']
                                        ) ?>
                                    </strong>

                                <?php else: ?>

                                    <span class="small">
                                        —
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

                                    Voir

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