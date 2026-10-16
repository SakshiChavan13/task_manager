
<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('Allow: PUT');
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
        'message' => 'You cannot update another user’s task.'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || !$input) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Provide a valid JSON object with fields to update.'
    ]);
}

$allowedFields = ['title', 'description', 'status', 'priority', 'due_date'];
$updates = [];
$params = ['id' => $id];

foreach ($input as $field => $value) {
    if (!in_array($field, $allowedFields, true)) {
        jsonResponse(422, [
            'success' => false,
            'message' => "Field '{$field}' cannot be updated."
        ]);
    }

    if ($field === 'title') {
        if (!is_string($value) || trim($value) === '' || mb_strlen(trim($value)) > 255) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Title must be a non-empty string of at most 255 characters.'
            ]);
        }

        $value = trim($value);
    }

    if ($field === 'description' && $value !== null && !is_string($value)) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Description must be a string or null.'
        ]);
    }

    if ($field === 'status' &&
        (!is_string($value) || !in_array($value, ['todo', 'in-progress', 'done'], true))) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Invalid task status.'
        ]);
    }

    if ($field === 'priority' &&
        (!is_string($value) || !in_array($value, ['low', 'medium', 'high'], true))) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Invalid task priority.'
        ]);
    }

    if ($field === 'due_date' && $value !== null) {
        $date = is_string($value)
            ? DateTime::createFromFormat('!Y-m-d', $value)
            : false;

        if (!$date || $date->format('Y-m-d') !== $value) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Due date must use YYYY-MM-DD format or be null.'
            ]);
        }
    }

    $updates[] = "{$field} = :{$field}";
    $params[$field] = $value;
}

if (!$updates) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'No fields to update.'
    ]);
}

$sql = 'UPDATE tasks SET ' . implode(', ', $updates) . ' WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

jsonResponse(200, [
    'success' => true,
    'message' => 'Task updated successfully.'
]);
