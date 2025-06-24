<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin/login.php');
    exit;
}

require_once '../db_connection/db_connection.php';

$user_id = (int) $_SESSION['user_id'];
$first_name = 'User';
$current_category = $_GET['category'] ?? 'all';
$current_priority = $_GET['priority'] ?? null;

// Get user details
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

// Get tasks based on current filters
// Get tasks based on current filters
$tasks = [];
try {
    $sql = "SELECT *, 
            DATE_FORMAT(due_date, '%Y-%m-%d') as date_only,
            DATE_FORMAT(due_date, '%H:%i') as time_only
            FROM tasks 
            WHERE user_id = ?";
    
    $params = [$user_id];
    
    if ($current_category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $current_category;
    }
    
    if ($current_priority) {
        $sql .= " AND priority = ?";
        $params[] = $current_priority;
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
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Get categories for filter
$categories = ['Work', 'Personal', 'Groceries'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Todolist | Dashboard</title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>
        
        .search-task-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px;
            justify-content: flex-start;
        }

        .search-task-item {
            display: flex;
            flex-direction: column;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            min-width: 200px;
            margin-bottom: 10px;
        }

        #calendar {
            max-width: 700px;
            font-size: 13px;
            margin: auto;
        }
        .fc-daygrid-day-frame {
            min-height: 10px !important;
            padding: 1px;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
        }
        .notification.error {
            background-color: #e74c3c;
        }
        .add-task-container {
            margin-top: 20px;
            text-align: center;
        }
        .profile-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color:rgba(7, 187, 223, 0.65);
            color: white;
            display: flex;
            border: 3px solid rgb(240, 243, 240);
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 10px;
        }

        .profile, .popup-header {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .profile img {
            display: none;
        }

        /* New styles for delete all button and suggestion sidebar */
        .delete-checked-btn {
            padding: 6px 12px;
            border-radius: 15px;
            border: 1px solid #ddd;
            background: #e74c3c;
            color: black;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: 260px;
        }

        .delete-checked-btn:hover {
            background: #c0392b;
        }

        .delete-checked-btn i {
            font-size: 12px;
        }
/* Right Sidebar */
.right-sidebar {
    width: 240px;
    padding: 20px;
    right: 0;
    left: auto;
    background-image: url(sideright.jpg);
   
    
}

.right-sidebar .suggestion-content {
    padding: 20px;
}

.right-sidebar h3 {
    color: #fffafa;
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    
}

.right-sidebar .suggestion-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.right-sidebar .suggestion-list li {
    padding: 12px 20px;
    font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    cursor: pointer;
    font-size: 15px;
    border-radius: 8px;
    background:rgba(255, 253, 253, 0.8);
    margin-bottom: 5px;
    color:rgb(34, 30, 30);
}

.right-sidebar .suggestion-list li:hover {
    background: #ddd5d561;
}

/* Adjust main content to fit between sidebars */
.main-content {
    margin-left: 250px;
    margin-right: 250px;
    max-width: calc(100% - 500px);
}
.smile {
    width: 200px;
    margin-top: 90px;
    display: block; /* Optional: ensures it's treated like a block if needed */
    margin-left: auto; /* Optional: center image horizontally */
    margin-right: auto;
    animation: rotate-animation 10s infinite linear;
	}

@keyframes rotate-animation {
	0% {
		transform: rotate(0deg);
  }
  50% {
		transform: rotate(180deg);
	}
	100% {
		transform: rotate(360deg);
	}
}
/* Target the plus icon inside the suggestion list */
.suggestion-list i.fa-plus {
    color: #0078D4;      /* Microsoft blue */
    font-size: 10px;     /* Slightly large icon */
    margin-right: 10px;  /* Space between + and text */
    vertical-align: middle;
}
.suggestion-list li {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 8px;
    background-color: #fff;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    font-family: sans-serif;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.suggestion-list li:hover {
    background-color: #f2f2f2;
}

.suggestion-list i.fa-plus {
    color: #0078D4;        /* Bright Microsoft blue */
    font-size: 20px;       /* Large icon */
    margin-right: 12px;    /* Space between icon and text */
    min-width: 20px;       /* Keeps alignment even if text shifts */
    display: inline-block;
}
.sparkle-icon {
    color:white ;
    font-size: 20px;
}
.sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-list li {
    display: flex;
    align-items: center;
    padding: 12px 10px;
    font-family: sans-serif;
    color: white;
}

.sidebar-list li i {
    font-size: 20px;
    margin-right: 10px;
    color: white;
}

.sidebar-list li.active i {
    color: #0078D4; /* Highlighted icon color */
}

.sidebar-list li a {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
}
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    z-index: 998; /* Below the popup */
}

.popup-sidebar {
    position: fixed;
    top: 10px;
    left: 8px; /* Sidebar width + margin */
    background-image: url(sideright.jpg);
    background-blend-mode: soft-light;
    padding: 20px;
    box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.2);
    z-index: 100;
    border-radius: 10px;
    width: 195px;
    display: none;
    height: 400px;
    z-index: 999; /* Above the overlay */
    transition: right 0.3s ease-in-out;
}





    </style>
</head>
<body>
<!-- Notification -->
<div id="notification" class="notification"></div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="profile" onclick="togglePopupSidebar()">
        <div class="profile-initial"><?= strtoupper(substr($first_name, 0, 1)) ?></div>
        <span class="username"><?= $first_name; ?></span>
    </div>

    <input type="text" placeholder="Search..." onkeyup="searchTasks(this.value)" />

    <nav>
    <ul class="sidebar-list">
    <li class="active">
        <a href="dashboard.php">
            <i class="fa-solid fa-bullseye"></i> Today
        </a>
    </li>
    <li>
        <a href="dashboard.php?category=Work">
            <i class="fa-regular fa-calendar-days"></i> Work
        </a>
    </li>
    <li>
        <a href="dashboard.php?category=Personal">
            <i class="fa-regular fa-note-sticky"></i> Personal
        </a>
    </li>
    <li>
        <a href="dashboard.php?category=Groceries">
            <i class="fa-regular fa-calendar-check"></i> Groceries
        </a>
    </li>
    <li onclick="toggleCalendarPopup()" style="color: white; cursor: pointer;">
        <i class="fa-regular fa-calendar"></i> Calendar
    </li>
</ul>

        <img src="smile.png" alt="" class="smile">
    </nav>
</div>

<!-- Calendar Popup -->
<div id="calendarPopup" class="calendar-popup" style="display: none;">
    <h2>Calendar View</h2>
    <div id="calendar"></div>
    <button onclick="toggleCalendarPopup()">Close</button>
</div>

<!-- Popup Sidebar -->
<div id="popupSidebar" class="popup-sidebar" style="display: none;">
    <div class="popup-header">
        <div class="profile-initial" style="background-color:rgba(15, 44, 49, 0.65);"><?= strtoupper(substr($first_name, 0, 1)) ?></div>
        <span class="username" style="color:rgb(255, 255, 255)"><?= $first_name; ?></span>
    </div>
    <ul>
        <li><a href="../myprofile/profile.php">My Profile</a></li>
        <li onclick="toggleCalendarPopup()" style="color:white">Calendar</li>
        <li><a href="../about/about.php">About</a></li>
        <li><a href="../support/support.php">Support</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div id="overlay" class="overlay" style="display: none;"></div>


<!-- Main Content -->
<div class="main-content">
    <h1>Good <?= getTimeOfDayGreeting(); ?>, <?= $first_name; ?>!</h1>

    <!-- Category Filters -->
    <div class="category-filter" id="categoryFilters">
        <button class="<?= ($current_category === 'all' && !$current_priority) ? 'active' : '' ?>" 
                onclick="window.location.href='dashboard.php'">All Tasks</button>
        <button class="<?= $current_priority === 'high' ? 'active' : '' ?>" 
                onclick="filterByPriority('high')"><i class="fas fa-exclamation-circle"></i> High Priority</button>
        <?php foreach ($categories as $category): ?>
            <button class="<?= $current_category === $category ? 'active' : '' ?>" 
                    onclick="filterByCategory('<?= htmlspecialchars($category) ?>')"><?= htmlspecialchars($category) ?></button>
        <?php endforeach; ?>
        <button class="delete-checked-btn" onclick="deleteAllCheckedTasks()">
            <i class="fas fa-trash-alt"></i> Delete Checked
        </button>
    </div>

    <!-- Tasks Section -->
    <div class="task-section" id="tasksList">
        <h2>
            <?= 
                $current_priority === 'high' ? "High Priority Tasks" :
                ($current_category === 'all' ? "All Tasks" : 
                htmlspecialchars($current_category) . " Tasks")
            ?>
            <span class="task-count">(<?= count($tasks) ?>)</span>
        </h2>
        <ul>
            <?php if (empty($tasks)): ?>
                <li class="no-tasks">No tasks found. Click + Add task to get started!</li>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <li class="task-item priority-<?= $task['priority'] ?>" 
                        data-task-id="<?= $task['id'] ?>" 
                        data-category="<?= htmlspecialchars($task['category'] ?? '') ?>"
                        data-due-date="<?= $task['due_date'] ?>">
                        <input type="checkbox" <?= $task['is_completed'] ? 'checked' : '' ?> 
                               onchange="toggleTaskCompletion(this, <?= $task['id'] ?>)">
                        <span class="desc"><?= htmlspecialchars($task['description']) ?></span>
                        <?php if ($task['due_date']): ?>
                            <span class="due-date" data-full-date="<?= $task['due_date'] ?>">
                                <i class="far fa-clock"></i> <?= date('M j, g:i a', strtotime($task['due_date'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($task['category']): ?>
                            <span class="task-category"><?= htmlspecialchars($task['category']) ?></span>
                        <?php endif; ?>
                        <div class="task-actions">
                            <select class="priority-select" onchange="setPriority(this, <?= $task['id'] ?>)">
                                <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                            <button class="edit-btn" onclick="editTask(<?= $task['id'] ?>)"><i class="fas fa-edit"></i></button>
                            <button class="dlt-btn" onclick="deleteTask(<?= $task['id'] ?>, this)"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <div class="add-task-container">
            <button class="add-task" onclick="showAddTaskModal('tasksList', '<?= htmlspecialchars($current_category === 'all' ? 'General' : $current_category) ?>')">
                <i class="fas fa-plus"></i> <?= $current_category === 'Groceries' ? 'Add Item' : 'Add Task' ?>
            </button>
        </div>
    </div>
</div>

<!-- Suggestion Sidebar -->
<!-- Add this right after the main-content div -->
<div class="sidebar right-sidebar">
    <div class="suggestion-content">
        <h3><i class="fa-solid fa-wand-magic-sparkles sparkle-icon"></i>

        Task Suggestions</h3>
        <ul class="suggestion-list">
            <li onclick="addSuggestedTask('Complete project report')"><i class="fa-solid fa-plus">&nbsp</i>Complete project report</li>
            <br>
            <li onclick="addSuggestedTask('Schedule team meeting')"><i class="fa-solid fa-plus"></i>Schedule team meeting</li>
            <br>
            <li onclick="addSuggestedTask('Review emails')"><i class="fa-solid fa-plus"></i>Review emails</li>
            <br>
            <li onclick="addSuggestedTask('Buy groceries')"><i class="fa-solid fa-plus"></i>Buy groceries</li>
            <br>
            <li onclick="addSuggestedTask('Exercise for 30 mins')"><i class="fa-solid fa-plus"></i>Appoinment</li>
        </ul>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<!-- Your Custom JS -->
<script src="scripts.js"></script>

</body>
</html>

<?php
function getTimeOfDayGreeting() {
    date_default_timezone_set('Asia/Kolkata');
    $hour = date('G');
    if ($hour < 12) return 'Morning';
    if ($hour < 17) return 'Afternoon';
    if ($hour < 22) return 'Evening';
    return 'Night';
}
?>