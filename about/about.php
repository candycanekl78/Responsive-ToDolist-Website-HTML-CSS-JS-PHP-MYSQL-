<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin/login.php');
    exit;
}

require_once '../db_connection/db_connection.php';

$user_id = (int) $_SESSION['user_id'];
$first_name = 'User';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Todolist | About</title>
    <link rel="stylesheet" href="../dashboard/style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        .about-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .about-header h1 {
            color: rgba(7, 187, 223, 0.65);
            margin-bottom: 10px;
        }
        
        .about-content {
            line-height: 1.6;
            color: #555;
        }
        
        .feature {
            display: flex;
            margin-bottom: 20px;
            align-items: flex-start;
        }
        
        .feature-icon {
            font-size: 24px;
            color: rgba(7, 187, 223, 0.65);
            margin-right: 15px;
            margin-top: 5px;
        }
        
        .feature-text h3 {
            margin-top: 0;
            color: #333;
        }
        
        .team-section {
            margin-top: 40px;
        }
        
        .team-member {
            text-align: center;
            margin: 20px 0;
        }
        
        .profile-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(7, 187, 223, 0.65);
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
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="profile" onclick="togglePopupSidebar()">
        <div class="profile-initial"><?= strtoupper(substr($first_name, 0, 1)) ?></div>
        <span class="username"><?= $first_name; ?></span>
    </div>

    <input type="text" placeholder="Search..." onkeyup="searchTasks(this.value)" />

    <nav>
        <ul>
            <li><a href="../dashboard/dashboard.php">Today</a></li>
            <li><a href="../dashboard/dashboard.php?category=Work">Work</a></li>
            <li><a href="../dashboard/dashboard.php?category=Personal">Personal</a></li>
            <li><a href="../dashboard/dashboard.php?category=Groceries">Groceries</a></li>
            <li onclick="toggleCalendarPopup()" style="color: white;">Calendar</li>
        </ul>
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
    <div class="about-container">
        <div class="about-header">
            <h1>About Our Todo List App</h1>
            <p>Simplifying your daily tasks with intuitive organization</p>
        </div>
        
        <div class="about-content">
            <p>Welcome to our Todo List application, designed to help you stay organized and productive in your daily life. Our mission is to provide a simple yet powerful tool to manage your tasks efficiently.</p>
            
            <h2>Key Features</h2>
            
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                <div class="feature-text">
                    <h3>Task Management</h3>
                    <p>Create, edit, and organize your tasks with ease. Set priorities and categories to keep everything in order.</p>
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="feature-text">
                    <h3>Calendar Integration</h3>
                    <p>View your tasks on a calendar to better plan your schedule and never miss important deadlines.</p>
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-bell"></i></div>
                <div class="feature-text">
                    <h3>Priority System</h3>
                    <p>Mark tasks as high, medium, or low priority to focus on what matters most.</p>
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-lock"></i></div>
                <div class="feature-text">
                    <h3>Secure & Private</h3>
                    <p>Your data is securely stored and only accessible to you with your account credentials.</p>
                </div>
            </div>
            
            <div class="team-section">
                <h2>Our Team</h2>
                <p>This application was developed by a dedicated team of developers passionate about productivity tools.</p>
                
                <div class="team-member">
                    <h3>Developer Team</h3>
                    <p>Creating solutions to make your life easier</p>
                </div>
            </div>
            
            <div class="version-info">
                <p><strong>Version:</strong> 1.0.0</p>
                <p><strong>Last Updated:</strong> <?= date('F Y') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<!-- Your Custom JS -->
<script src="../dashboard/scripts.js"></script>


</body>
</html>