
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

// Allow DELETE requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');

    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Get the task ID from the URL.
$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'A valid task ID is required.'
    ]);
}

try {
    // Check whether the task exists.
    $check = $pdo->prepare(
        'SELECT id FROM tasks WHERE id = :id'
    );

    $check->execute(['id' => $id]);

    if (!$check->fetch()) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'Task not found.'
        ]);
    }

    // Delete the task.
    $stmt = $pdo->prepare(
        'DELETE FROM tasks WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Task deleted successfully.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Failed to delete task.'
    ]);
}
