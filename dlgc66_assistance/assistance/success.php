<?php
require __DIR__ . '/lib/bootstrap.php';

$ticketCode = $_SESSION['created_ticket_code'] ?? null;
unset($_SESSION['created_ticket_code']);

if (!$ticketCode) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Demande enregistrée - DLGC</title>
  <link rel="stylesheet" href="assets/css/assistance.css">
</head>
<body>
  <main class="page-shell">
    <div class="topbar">
      <div class="brand">DLGC • Assistance client</div>
      <a href="../111111.html">← Retour au site</a>
    </div>

    <section class="card">
      <div class="alert alert-success"><strong>Votre demande a été enregistrée avec succès.</strong></div>
      <h1>Numéro de demande</h1>
      <div class="ticket-code"><?= e((string) $ticketCode) ?></div>
      <p class="lead">Conservez ce numéro comme référence. Notre équipe technique pourra vous contacter au numéro indiqué.</p>
      <div class="actions">
        <a class="btn btn-primary" href="index.php">Créer une autre demande</a>
        <a class="btn btn-light" href="../111111.html">Retour au site DLGC</a>
      </div>
    </section>
  </main>
</body>
</html>
