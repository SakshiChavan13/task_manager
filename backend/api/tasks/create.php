
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON request body.'
    ]);
}

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$status = $input['status'] ?? 'todo';
$priority = $input['priority'] ?? 'medium';
$dueDate = $input['due_date'] ?? null;
$userId = filter_var(
    $input['user_id'] ?? null,
    FILTER_VALIDATE_INT
);

$allowedStatuses = ['todo', 'in-progress', 'done'];
$allowedPriorities = ['low', 'medium', 'high'];

if ($title === '' || mb_strlen($title) > 255) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Title is required and must be at most 255 characters.'
    ]);
}

if (!is_string($description)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Description must be a string.'
    ]);
}

if (!in_array($status, $allowedStatuses, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid task status.'
    ]);
}

if (!in_array($priority, $allowedPriorities, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid task priority.'
    ]);
}

if ($userId === false || $userId === null || $userId < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'A valid user_id is required.'
    ]);
}

if ($dueDate !== null) {
    $date = is_string($dueDate)
        ? DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate)
        : false;

    if (
        !$date ||
        $date->format('Y-m-d') !== $dueDate
    ) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Due date must use YYYY-MM-DD format.'
        ]);
    }
}

try {
    // Temporary: validate that the owner exists.
    // Authentication will determine the authorized owner later.
    $userStmt = $pdo->prepare(
        'SELECT id FROM users WHERE id = :id'
    );
    $userStmt->execute(['id' => $userId]);

    if (!$userStmt->fetch()) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'The specified user does not exist.'
        ]);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO tasks
            (user_id, title, description, status, priority, due_date)
         VALUES
            (:user_id, :title, :description, :status, :priority, :due_date)'
    );

    $stmt->execute([
        'user_id' => $userId,
        'title' => $title,
        'description' => $description !== '' ? $description : null,
        'status' => $status,
        'priority' => $priority,
        'due_date' => $dueDate
    ]);

    $taskId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT id, user_id, title, description, status,
                priority, due_date, created_at, updated_at
         FROM tasks
         WHERE id = :id'
    );

    $stmt->execute(['id' => $taskId]);

    jsonResponse(201, [
        'success' => true,
        'message' => 'Task created successfully.',
        'data' => $stmt->fetch()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Failed to create task.'
    ]);
}
