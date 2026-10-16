
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$currentUser = authenticateRequest();

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Request body must be valid JSON.'
    ]);
}

$title = isset($input['title']) && is_string($input['title'])
    ? trim($input['title'])
    : '';

$description = $input['description'] ?? null;
$status = $input['status'] ?? 'todo';
$priority = $input['priority'] ?? 'medium';
$dueDate = $input['due_date'] ?? null;

if ($title === '' || mb_strlen($title) > 255) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Title is required and must be at most 255 characters.'
    ]);
}

if ($description !== null && !is_string($description)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Description must be a string or null.'
    ]);
}

if (!is_string($status) ||
    !in_array($status, ['todo', 'in-progress', 'done'], true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid task status.'
    ]);
}

if (!is_string($priority) ||
    !in_array($priority, ['low', 'medium', 'high'], true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid task priority.'
    ]);
}

if ($dueDate !== null) {
    $date = is_string($dueDate)
        ? DateTime::createFromFormat('!Y-m-d', $dueDate)
        : false;

    if (!$date || $date->format('Y-m-d') !== $dueDate) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Due date must use YYYY-MM-DD format or be null.'
        ]);
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO tasks (user_id, title, description, status, priority, due_date)
     VALUES (:user_id, :title, :description, :status, :priority, :due_date)'
);

$stmt->execute([
    'user_id' => $currentUser->id,
    'title' => $title,
    'description' => $description,
    'status' => $status,
    'priority' => $priority,
    'due_date' => $dueDate
]);

$id = (int) $pdo->lastInsertId();

jsonResponse(201, [
    'success' => true,
    'message' => 'Task created successfully.',
    'data' => [
        'id' => $id,
        'user_id' => $currentUser->id,
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'priority' => $priority,
        'due_date' => $dueDate
    ]
]);
