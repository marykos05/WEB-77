<?php
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (начало) ===
header('Content-Type: text/html; charset=UTF-8');
header_remove('X-Powered-By');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (конец) ===

// === ЗАЩИТА ОТ XSS (начало) ===
function safeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
// === ЗАЩИТА ОТ XSS (конец) ===

// === ЗАЩИТА ОТ CSRF (начало) ===
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// === ЗАЩИТА ОТ CSRF (конец) ===

// Оригинальный код формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === ЗАЩИТА ОТ CSRF (проверка) ===
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Неверный CSRF-токен');
    }
    
    // === ЗАЩИТА ОТ SQL INJECTION (начало) ===
    try {
        $db = new PDO('mysql:host=localhost;dbname=u69186', 'u69186', '8849997', [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $db->prepare("INSERT INTO application (fio, phone, email, birthdate, gender, biography, contract_accepted) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            safeOutput($_POST['fio']),
            safeOutput($_POST['tel']),
            filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            $_POST['date'],
            $_POST['gender'],
            safeOutput($_POST['bio']),
            $_POST['check'] ? 1 : 0
        ]);
        
        // Сохранение в куки
        $formData = [
            'fio' => safeOutput($_POST['fio']),
            'tel' => safeOutput($_POST['tel']),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'date' => $_POST['date'],
            'gender' => $_POST['gender'],
            'bio' => safeOutput($_POST['bio']),
            'plang' => $_POST['plang'] ?? []
        ];
        setcookie('form_data', json_encode($formData), [
            'expires' => time() + 3600 * 24 * 365,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        header('Location: index.php?save=1');
        exit;
    } catch (PDOException $e) {
        // === ЗАЩИТА ОТ INFORMATION DISCLOSURE (начало) ===
        error_log($e->getMessage());
        $_SESSION['error'] = 'Ошибка сохранения данных';
        header('Location: index.php');
        exit;
        // === ЗАЩИТА ОТ INFORMATION DISCLOSURE (конец) ===
    }
    // === ЗАЩИТА ОТ SQL INJECTION (конец) ===
}

// Оригинальный HTML-код формы с защитой XSS
?>
<!DOCTYPE html>
<html>
<head>
    <title>Форма</title>
</head>
<body>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= safeOutput($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= safeOutput($_SESSION['csrf_token']) ?>">
        
        <input type="text" name="fio" value="<?= safeOutput($_POST['fio'] ?? '') ?>">
        <!-- Остальные поля формы -->
        <button type="submit">Отправить</button>
    </form>
</body>
</html>