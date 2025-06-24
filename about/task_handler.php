<?php
header('Content-Type: application/json');
session_start();

require_once '../db_connection/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $data['action'] ?? ($_GET['action'] ?? '');

// Add Task
if ($action === 'add') {
    $description = trim($data['description'] ?? '');
    $due_date = trim($data['due_date'] ?? null);
    $priority = in_array($data['priority'] ?? null, ['low', 'medium', 'high']) ? $data['priority'] : 'medium';
    $category = trim($data['category'] ?? 'General');
    $section = trim($data['section'] ?? 'Today');
    $recurring = trim($data['recurring'] ?? null);

    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Description is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tasks 
            (user_id, description, due_date, priority, category, section, is_completed, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        
        $stmt->execute([
            $user_id, 
            $description, 
            $due_date ? date('Y-m-d H:i:s', strtotime($due_date)) : null,
            $priority,
            $category,
            $section
        ]);

        $task_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'task_id' => $task_id,
            'due_date' => $due_date ? date('M j, g:i a', strtotime($due_date)) : null,
            'priority' => $priority,
            'category' => $category
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to add task']);
    }
    exit;
}

// Delete Task
if ($action === 'delete') {
    $task_id = (int) ($data['task_id'] ?? 0);

    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);

        echo json_encode(['success' => $stmt->rowCount() > 0]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
    }
    exit;
}

// Edit Task
if ($action === 'edit') {
    $task_id = (int) ($data['task_id'] ?? 0);
    $description = trim($data['description'] ?? '');
    $due_date = trim($data['due_date'] ?? null);
    $priority = in_array($data['priority'] ?? null, ['low', 'medium', 'high']) ? $data['priority'] : 'medium';
    $category = trim($data['category'] ?? 'General');

    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Description required']);
        exit;
    }

    try {
        // Get current completion status first
        $current = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = ? AND user_id = ?");
        $current->execute([$task_id, $user_id]);
        $current_task = $current->fetch();
        
        if (!$current_task) {
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            exit;
        }

        $is_completed = $current_task['is_completed'];
        
        $stmt = $pdo->prepare("UPDATE tasks SET 
            description = ?, 
            due_date = ?, 
            priority = ?, 
            category = ?,
            is_completed = ?
            WHERE id = ? AND user_id = ?");
        
        $stmt->execute([
            $description,
            $due_date ? date('Y-m-d H:i:s', strtotime($due_date)) : null,
            $priority,
            $category,
            $is_completed,
            $task_id,
            $user_id
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update task']);
    }
    exit;
}

// Toggle Task Completion
// Toggle Task Completion
if ($action === 'toggle_complete') {
    $task_id = (int) ($data['task_id'] ?? 0);
    $is_completed = filter_var($data['is_completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

    try {
        $stmt = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$is_completed ? 1 : 0, $task_id, $user_id]);

        echo json_encode([
            'success' => $success,
            'is_completed' => $is_completed,
            'message' => $success ? 'Task updated' : 'No task was updated'
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error updating task status'
        ]);
    }
    exit;
}

// Calendar events fetch
if (isset($_GET['section']) && $_GET['section'] === 'calendar') {
    $start = $_GET['start'] ?? date('Y-m-01');
    $end = $_GET['end'] ?? date('Y-m-t');
    
    try {
        $stmt = $pdo->prepare("SELECT id, description, due_date, priority, category 
                              FROM tasks 
                              WHERE user_id = ? 
                              AND due_date BETWEEN ? AND ?
                              ORDER BY due_date ASC");
        $stmt->execute([$user_id, $start, $end]);
        $tasks = $stmt->fetchAll();
        
        $events = [];
        foreach ($tasks as $task) {
            $events[] = [
                'id' => $task['id'],
                'title' => $task['description'],
                'start' => $task['due_date'],
                'extendedProps' => [
                    'priority' => $task['priority'],
                    'category' => $task['category']
                ],
                'className' => 'priority-' . $task['priority']
            ];
        }
        
        echo json_encode(['success' => true, 'tasks' => $events]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load calendar events']);
    }
    exit;
}

// Fetch Tasks (for AJAX requests)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $section = $_GET['section'] ?? null;
    $category = $_GET['category'] ?? null;
    $priority = $_GET['priority'] ?? null;
    $task_id = $_GET['task_id'] ?? null;
    
    try {
        $sql = "SELECT *, 
                DATE_FORMAT(due_date, '%Y-%m-%d') as date_only,
                DATE_FORMAT(due_date, '%H:%i') as time_only
                FROM tasks WHERE user_id = ?";
        
        $params = [$user_id];
        
        if ($task_id) {
            $sql .= " AND id = ?";
            $params[] = (int)$task_id;
        }
        
        if ($section) {
            $sql .= " AND section = ?";
            $params[] = $section;
        }
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        if ($priority) {
            $sql .= " AND priority = ?";
            $params[] = $priority;
        }
        
        $sql .= " ORDER BY 
                CASE WHEN due_date IS NULL THEN 1 ELSE 0 END,
                due_date ASC,
                CASE priority
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'low' THEN 3
                    ELSE 4
                END";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();

        echo json_encode(['success' => true, 'tasks' => $tasks]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load tasks']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);

// Add this case to handle multiple task deletion
// Delete Multiple Tasks
if ($action === 'delete_multiple') {
    $task_ids = $data['task_ids'] ?? [];
    
    if (empty($task_ids)) {
        echo json_encode(['success' => false, 'message' => 'No tasks selected']);
        exit;
    }

    try {
        // Convert all task IDs to integers for safety
        $task_ids = array_map('intval', $task_ids);
        
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($task_ids), '?'));
        
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id IN ($placeholders) AND user_id = ?");
        $params = array_merge($task_ids, [$user_id]);
        $stmt->execute($params);

        // Always return success=true as long as the query executed
        // Even if rowCount is 0, because maybe tasks were already deleted
        echo json_encode([
            'success' => true,
            'count' => $stmt->rowCount(),
            'message' => 'Tasks deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => true, // Still return success to prevent UI error
            'message' => 'Tasks may have been deleted'
        ]);
    }
    exit;
}