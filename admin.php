<?php
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (начало) ===
header('Content-Type: text/html; charset=UTF-8');
header_remove('X-Powered-By');
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (конец) ===

// === ЗАЩИТА ОТ CSRF (начало) ===
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true
]);

if (!isset($_SESSION['admin'])) {
    die('Доступ запрещен');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// === ЗАЩИТА ОТ CSRF (конец) ===

// Оригинальный код админки
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === ЗАЩИТА ОТ CSRF (проверка) ===
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Неверный CSRF-токен');
    }

    // === ЗАЩИТА ОТ SQL INJECTION (начало) ===
    try {
        $db = new PDO('mysql:host=localhost;dbname=u69186', 'u69186', '8849997', [
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        if (isset($_POST['delete'])) {
            $id = (int)$_POST['id'];
            $db->beginTransaction();
            
            $stmt = $db->prepare("DELETE FROM application_programming_language WHERE application_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            $_SESSION['message'] = 'Пользователь удален';
        }
        
        header('Location: admin.php');
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        error_log($e->getMessage());
        $_SESSION['error'] = 'Ошибка при удалении';
        header('Location: admin.php');
        exit;
    }
    // === ЗАЩИТА ОТ SQL INJECTION (конец) ===
}

// Оригинальный HTML-код админ-панели
?>
<!DOCTYPE html>
<html>
<head>
    <title>Админ-панель</title>
</head>
<body>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="message"><?= safeOutput($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <!-- Таблица с пользователями -->
    <form method="POST" action="admin.php">
        <input type="hidden" name="csrf_token" value="<?= safeOutput($_SESSION['csrf_token']) ?>">
        <!-- Остальной интерфейс админки -->
    </form>
</body>
</html>