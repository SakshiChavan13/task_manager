
<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/database.php';

try {
    $result = $pdo->query(
        'SELECT DATABASE() AS database_name'
    )->fetch();

    $tables = $pdo->query(
        "SHOW TABLES"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "Database connection successful!\n";
    echo "Database: " . $result['database_name'] . "\n";
    echo "Tables found: " . implode(', ', $tables) . "\n";
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Database query failed. Check the PHP error log.";
}
