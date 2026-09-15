<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Algiers');

// Session sécurisée autant que possible, compatible avec localhost HTTP.
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $https,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function ensure_tables_exist(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $pdo->query('SELECT 1 FROM admins LIMIT 1');
        $pdo->query('SELECT 1 FROM assistance_requests LIMIT 1');
    } catch (PDOException $e) {
        $sqlFile = __DIR__ . '/../database/dlgc_assistance.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            if ($sql !== false) {
                // Nettoyage des commentaires SQL avant le découpage
                $sqlClean = preg_replace('/--.*$/m', '', $sql);
                $sqlClean = preg_replace('/\/\*.*?\*\//s', '', $sqlClean);
                $queries = array_filter(array_map('trim', explode(';', $sqlClean)));
                foreach ($queries as $query) {
                    if ($query !== '') {
                        try {
                            $pdo->exec($query);
                        } catch (PDOException $ex) {
                            // Ignorer si la table existe déjà
                        }
                    }
                }
            }
        }
    }
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/../config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        ensure_tables_exist($pdo);
    } catch (PDOException $e) {
        // En cas de base non existante (erreur MySQL 1049), création automatique
        if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
            try {
                $rootDsn = sprintf(
                    'mysql:host=%s;port=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['charset']
                );
                $rootPdo = new PDO($rootDsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $safeDbName = str_replace('`', '``', $config['dbname']);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                $pdo = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                ensure_tables_exist($pdo);
                return $pdo;
            } catch (PDOException $ex) {
                // Fallthrough en cas d'échec
            }
        }

        http_response_code(500);
        exit(
            '<h2>Connexion à la base de données impossible.</h2>' .
            '<p>Vérifiez que MySQL est démarré dans XAMPP, que la base <strong>dlgc_assistance</strong> existe, puis vérifiez le fichier <code>assistance/config/database.php</code>.</p>'
        );
    }

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        exit('Session expirée ou requête invalide. Revenez à la page précédente puis réessayez.');
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']) && is_numeric($_SESSION['admin_id']);
}

function require_admin(): void
{
    // لا توجد جلسة Admin
    if (!is_admin_logged_in()) {
        redirect('login.php');
    }

    $pdo = db();

    // التحقق من أن الحساب ما زال موجودًا وفعالًا
    $stmt = $pdo->prepare(
        'SELECT
            id,
            display_name,
            role,
            is_active
         FROM admins
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => current_admin_id(),
    ]);

    $admin = $stmt->fetch();

    // الحساب حُذف أو أصبح غير فعال
    if (!$admin || (int) $admin['is_active'] !== 1) {

        $_SESSION = [];

        session_destroy();

        redirect('login.php');
    }

    // تحديث المعلومات الموجودة في Session
    // إذا غُيّر الاسم أو الدور من قاعدة البيانات
    $_SESSION['admin_name'] =
        (string) $admin['display_name'];

    $_SESSION['admin_role'] =
        (string) $admin['role'];
}

function current_admin_id(): int
{
    return (int) ($_SESSION['admin_id'] ?? 0);
}
function current_admin_role(): string
{
    return (string) ($_SESSION['admin_role'] ?? 'employee');
}

function is_super_admin(): bool
{
    return is_admin_logged_in() && current_admin_role() === 'super_admin';
}

function require_super_admin(): void
{
    require_admin();

    if (!is_super_admin()) {
        http_response_code(403);
        exit('Accès interdit. Cette page est réservée au Super Admin.');
    }
}

function status_label(string $status): string
{
    return match ($status) {
        'nouveau' => 'Nouveau',
        'en_cours' => 'En cours',
        'resolu' => 'Résolu',
        default => 'Inconnu',
    };
}

function status_class(string $status): string
{
    return match ($status) {
        'nouveau' => 'status-new',
        'en_cours' => 'status-progress',
        'resolu' => 'status-resolved',
        default => '',
    };
}
