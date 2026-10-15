
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('Allow: PUT');

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

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'A valid task ID is required.'
    ]);
}

$allowedStatuses = ['todo', 'in-progress', 'done'];
$allowedPriorities = ['low', 'medium', 'high'];

try {
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

    $fields = [];
    $params = ['id' => $id];

    if (array_key_exists('title', $input)) {
        if (!is_string($input['title'])) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Title must be a string.'
            ]);
        }

        $title = trim($input['title']);

        if ($title === '' || mb_strlen($title) > 255) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Title must be between 1 and 255 characters.'
            ]);
        }

        $fields[] = 'title = :title';
        $params['title'] = $title;
    }

    if (array_key_exists('description', $input)) {
        if (
            $input['description'] !== null &&
            !is_string($input['description'])
        ) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Description must be a string or null.'
            ]);
        }

        $fields[] = 'description = :description';
        $params['description'] = $input['description'] === null
            ? null
            : trim($input['description']);
    }

    if (array_key_exists('status', $input)) {
        if (
            !is_string($input['status']) ||
            !in_array($input['status'], $allowedStatuses, true)
        ) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Invalid task status.'
            ]);
        }

        $fields[] = 'status = :status';
        $params['status'] = $input['status'];
    }

    if (array_key_exists('priority', $input)) {
        if (
            !is_string($input['priority']) ||
            !in_array($input['priority'], $allowedPriorities, true)
        ) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'Invalid priority.'
            ]);
        }

        $fields[] = 'priority = :priority';
        $params['priority'] = $input['priority'];
    }

    if (array_key_exists('due_date', $input)) {
        $dueDate = $input['due_date'];

        if ($dueDate !== null) {
            $date = is_string($dueDate)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate)
                : false;

            if (!$date || $date->format('Y-m-d') !== $dueDate) {
                jsonResponse(422, [
                    'success' => false,
                    'message' => 'Due date must be YYYY-MM-DD or null.'
                ]);
            }
        }

        $fields[] = 'due_date = :due_date';
        $params['due_date'] = $dueDate;
    }

    if (empty($fields)) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Provide at least one field to update.'
        ]);
    }

    $sql = 'UPDATE tasks SET ' . implode(', ', $fields)
        . ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $stmt = $pdo->prepare(
        'SELECT id, user_id, title, description, status,
                priority, due_date, created_at, updated_at
         FROM tasks WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Task updated successfully.',
        'data' => $stmt->fetch()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Failed to update task.'
    ]);
}
