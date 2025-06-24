<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<li>User not logged in</li>";
    exit;
}

require_once '../db_connection/db_connection.php';

$user_id = $_SESSION['user_id'];
$query = trim($_POST['keyword'] ?? '');

if ($query === '') {
    echo "<li>Please enter a search term.</li>";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, description, time FROM tasks WHERE user_id = ? AND description LIKE ? ORDER BY id DESC");
    $stmt->execute([$user_id, "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "<li>No matching tasks found.</li>";
        exit;
    }

    // Use unique container for search results
    echo "<div class='search-task-container'>";

    foreach ($results as $task) {
        $id = (int)$task['id'];
        $description = htmlspecialchars($task['description']);
        $time = htmlspecialchars($task['time']);

        echo "<div class='search-task-item'>
                <input type='checkbox' onchange='toggleTask(this)'>
                <span class='desc'>$description</span>
                <span class='time'>$time</span>
                <button class='dlt-btn' onclick='deleteTask($id, this)'>Delete</button>
                <button class='edit-btn' onclick='editTask(this, $id)'>Edit</button>
              </div>";
    }

    echo "</div>";

} catch (PDOException $e) {
    echo "<li>Error fetching tasks: " . htmlspecialchars($e->getMessage()) . "</li>";
}
?>
