<?php
require __DIR__ . '/lib/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

// Petit piège anti-bot invisible pour les humains.
if (!empty($_POST['website'])) {
    http_response_code(400);
    exit('Requête invalide.');
}

$clientCode = trim((string) ($_POST['client_code'] ?? ''));
$companyName = trim((string) ($_POST['company_name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

$errors = [];

if (mb_strlen($clientCode) > 50) {
    $errors[] = 'Le code client est trop long.';
}
if ($companyName === '' || mb_strlen($companyName) > 150) {
    $errors[] = 'Le nom de la société est obligatoire et doit contenir au maximum 150 caractères.';
}
if ($phone === '' || mb_strlen($phone) > 40) {
    $errors[] = 'Le numéro de téléphone est obligatoire.';
}
if ($subject === '' || mb_strlen($subject) > 180) {
    $errors[] = 'Le sujet est obligatoire et doit contenir au maximum 180 caractères.';
}
if ($description === '' || mb_strlen($description) > 5000) {
    $errors[] = 'La description est obligatoire et doit contenir au maximum 5000 caractères.';
}

if ($errors) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'client_code' => $clientCode,
        'company_name' => $companyName,
        'phone' => $phone,
        'subject' => $subject,
        'description' => $description,
    ];
    redirect('index.php');
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO assistance_requests (client_code, company_name, phone, subject, description)
         VALUES (:client_code, :company_name, :phone, :subject, :description)'
    );
    $stmt->execute([
        ':client_code' => $clientCode !== '' ? $clientCode : null,
        ':company_name' => $companyName,
        ':phone' => $phone,
        ':subject' => $subject,
        ':description' => $description,
    ]);

    $id = (int) $pdo->lastInsertId();
    if ($id <= 0) {
        throw new RuntimeException('Impossible de récupérer l’identifiant de la demande.');
    }
    $ticketCode = sprintf('AST-%s-%06d', date('Y'), $id);

    $update = $pdo->prepare('UPDATE assistance_requests SET ticket_code = :ticket_code WHERE id = :id');
    $update->execute([
        ':ticket_code' => $ticketCode,
        ':id' => $id,
    ]);

    $pdo->commit();

    // On garde le code en session pour la page de confirmation.
    $_SESSION['created_ticket_code'] = $ticketCode;

    redirect('success.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    exit('Une erreur est survenue pendant l’enregistrement. Veuillez réessayer.');
}
