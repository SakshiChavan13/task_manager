
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');
    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$currentUser = authenticateRequest();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'A valid task ID is required.'
    ]);
}

$stmt = $pdo->prepare('SELECT id, user_id FROM tasks WHERE id = :id');
$stmt->execute(['id' => $id]);
$task = $stmt->fetch();

if (!$task) {
    jsonResponse(404, [
        'success' => false,
        'message' => 'Task not found.'
    ]);
}

if (
    $currentUser->role !== 'admin' &&
    (int) $task['user_id'] !== $currentUser->id
) {
    jsonResponse(403, [
        'success' => false,
        'message' => 'You cannot delete another user’s task.'
    ]);
}

$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
$stmt->execute(['id' => $id]);

jsonResponse(200, [
    'success' => true,
    'message' => 'Task deleted successfully.'
]);
