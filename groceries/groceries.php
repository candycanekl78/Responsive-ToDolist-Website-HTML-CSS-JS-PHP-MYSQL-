<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin/login.php');
    exit;
}

require_once '../db_connection/db_connection.php';

$user_id = (int) $_SESSION['user_id'];
$first_name = 'User';

try {
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $first_name = htmlspecialchars($user['first_name']);
    }
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY time ASC");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $tasks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Todolist | Dashboard</title>
    <link rel="stylesheet" href="style.css"/>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />

        <!-- FullCalendar JS -->

<style>
    
    #calendar {
    max-width: 700px; /* Increase width */
    font-size: 13px;   /* Slightly smaller text for compact look */
    margin: auto;
}

}

/* Optional: Reduce cell height */
.fc-daygrid-day-frame {
    min-height: 10px !important;
    padding: 1px;
}
.main-content .logout-link {
    padding: 10px 20px;
    display: inline-block;
    text-decoration: none;
    background-color: #e74c3c;
    color: #fff;
    border-radius: 5px;
}

</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile" onclick="togglePopupSidebar()">
        <img src="dp.jpg" alt="Profile" onerror="this.src='default.jpg';"/>
        <span class="username"><?= $first_name; ?></span>
    </div>

    <input type="text" placeholder="Search..." onkeyup="searchTasks(this.value)" />

    <nav>
        <ul>
            <li class="active">Today</li>
            <li>Upcoming</li>
            <li onclick="toggleCalendarPopup()">Calendar</li>
            <li>Inbox</li>
            <li>Groceries</li>
        </ul>
    </nav>
<br>
  
<br>
  
</div>

<!-- Calendar Popup -->
<!-- Calendar Popup -->
<div id="calendarPopup" class="calendar-popup" style="display: none;">
    <h2>Calendar View</h2>
    <div id="calendar"></div>
    <button onclick="toggleCalendarPopup()">Close</button>
</div>

<!-- Popup Sidebar -->
<div id="popupSidebar" class="popup-sidebar" style="display: none;">
    <div class="popup-header">
        <img src="dp.jpg" alt="Profile" onerror="this.src='default.jpg';"/>
        <span class="username"><?= $first_name; ?></span>
    </div>
    <ul>
        <li><a href="/myprofile/profile.php">My Profile</a></li>
        <li><a href="#">Archived Tasks</a></li>
        <li onclick="toggleCalendarPopup()">Calendar</li>
        <li><a href="../about/about.php">About</a></li>
        <li><a href="../support/support.php">Support</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Good Afternoon, <?= $first_name; ?>!</h1>

    <!--Groceries -->
    <div class="task-section" id="myProjects">
        <h2>My Projects</h2>
        <ul id="projectsList">
            <?php
            $found = false;
            foreach ($tasks as $task):
                if (strtolower($task['section']) === 'my projects'):
                    $found = true; ?>
                    <li>
                        <input type="checkbox" onchange="toggleTask(this)" />
                        <span class="desc"><?= htmlspecialchars($task['description']) ?></span>
                        <span class="time"><?= htmlspecialchars($task['time']) ?></span>
                        <button class="dlt-btn" onclick="deleteTask(<?= (int)$task['id'] ?>, this)">Delete</button>
                        <button class="edit-btn" onclick="editTask(this, <?= (int)$task['id'] ?>)">Edit</button>
                    </li>
                <?php endif;
            endforeach;
            if (!$found): ?>
                <li>No tasks found. Click + Add task to get started!</li>
            <?php endif; ?>
        </ul>
        <button class="add-task" onclick="addTask('projectsList', 'my projects')">+ Add task</button>
    </div>

  
<!-- FullCalendar JS (placed before your own script) -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<!-- Your Custom JS -->
<script src="scripts.js"></script>

<!-- Initialize FullCalendar -->
<script>
let calendar;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 400,
        events: [
            {
                title: 'Workout',
                start: '2025-04-21',
            },
            {
                title: 'Appoinment',
                start: '2025-04-25',
            }
        ]
    });
});

// Toggle function
function toggleCalendarPopup() {
    const popup = document.getElementById("calendarPopup");
    const isVisible = popup.style.display === "block";

    popup.style.display = isVisible ? "none" : "block";

    // Only render calendar when shown
    if (!isVisible && calendar) {
        setTimeout(() => {
            calendar.render();
        }, 10); // small delay allows DOM to settle
    }
}
</script>



<div id="backdrop" style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9;"></div>

</body>
</html>
