<?php
// === ЗАЩИТА ОТ UPLOAD (начало) ===
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    // Проверка аутентификации
    if (empty($_SESSION['user_id'])) {
        die('Доступ запрещен');
    }

    // Параметры файла
    $file = $_FILES['file'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Проверка расширения
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        die('Недопустимое расширение файла');
    }

    // Проверка MIME-типа
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedMimeTypes)) {
        die('Недопустимый тип файла');
    }

    // Проверка размера
    if ($file['size'] > $maxSize) {
        die('Файл слишком большой');
    }

    // Генерация нового имени
    $newName = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadPath = '/var/www/private/uploads/' . $newName;

    // Перемещение файла
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Сохранение информации в БД
        try {
            $db = new PDO('mysql:host=localhost;dbname=u69186', 'u69186', '8849997', [
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            $stmt = $db->prepare("INSERT INTO uploads (user_id, filename, original_name) VALUES (?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $newName,
                safeOutput($file['name'])
            ]);
            
            echo 'Файл успешно загружен';
        } catch (PDOException $e) {
            unlink($uploadPath);
            error_log($e->getMessage());
            die('Ошибка при сохранении файла');
        }
    } else {
        die('Ошибка загрузки файла');
    }
}
// === ЗАЩИТА ОТ UPLOAD (конец) ===
?>