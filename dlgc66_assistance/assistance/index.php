<?php
require __DIR__ . '/lib/bootstrap.php';

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Demande d’assistance - DLGC</title>
  <link rel="stylesheet" href="assets/css/assistance.css">
</head>
<body>
  <main class="page-shell">
    <div class="topbar">
      <div class="brand">DLGC • Assistance client</div>
      <a href="../111111.html">← Retour au site</a>
    </div>

    <section class="card">
      <h1>🎧 Demande d’assistance</h1>
      <p class="lead">Décrivez votre problème. Après l’envoi, un numéro de demande sera créé automatiquement et notre équipe technique pourra traiter votre demande.</p>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <strong>Veuillez corriger les points suivants :</strong>
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= e($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="submit.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="honeypot" aria-hidden="true">
          <label for="website">Ne pas remplir</label>
          <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="form-grid">
          <div>
            <label for="client_code">Code client <span class="optional">(facultatif)</span></label>
            <input id="client_code" name="client_code" type="text" maxlength="50" value="<?= e($old['client_code'] ?? '') ?>" placeholder="Ex. CLT-1024">
          </div>

          <div>
            <label for="company_name">Nom de la société *</label>
            <input id="company_name" name="company_name" type="text" maxlength="150" required value="<?= e($old['company_name'] ?? '') ?>">
          </div>

          <div>
            <label for="phone">Téléphone *</label>
            <input id="phone" name="phone" type="tel" maxlength="40" required value="<?= e($old['phone'] ?? '') ?>" placeholder="Ex. 0550 00 00 00">
          </div>

          <div>
            <label for="subject">Sujet *</label>
            <input id="subject" name="subject" type="text" maxlength="180" required value="<?= e($old['subject'] ?? '') ?>" placeholder="Ex. Problème de stock">
          </div>

          <div class="field-full">
            <label for="description">Description du problème *</label>
            <textarea id="description" name="description" maxlength="5000" required placeholder="Expliquez le problème le plus clairement possible…"><?= e($old['description'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-green" type="submit">Envoyer la demande</button>
          <span class="small">Les champs marqués * sont obligatoires.</span>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
