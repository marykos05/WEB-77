<?php
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (начало) ===
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Доступ запрещен');
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log('/var/log/php_errors.log');
// === ЗАЩИТА ОТ INFORMATION DISCLOSURE (конец) ===

// === ЗАЩИТА ОТ INCLUDE (начало) ===
define('APP_ROOT', dirname(__DIR__));
define('DB_CONFIG', [
    'host' => 'localhost',
    'dbname' => 'u69186',
    'user' => 'u69186',
    'pass' => '8849997'
]);
// === ЗАЩИТА ОТ INCLUDE (конец) ===

// Функция безопасного подключения к БД
function getDB() {
    static $db = null;
    if ($db === null) {
        // === ЗАЩИТА ОТ SQL INJECTION (начало) ===
        try {
            $db = new PDO(
                "mysql:host=" . DB_CONFIG['host'] . ";dbname=" . DB_CONFIG['dbname'],
                DB_CONFIG['user'],
                DB_CONFIG['pass'],
                [
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die('Ошибка подключения к базе данных');
        }
        // === ЗАЩИТА ОТ SQL INJECTION (конец) ===
    }
    return $db;
}