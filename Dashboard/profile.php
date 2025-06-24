<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin/login.php');
    exit;
}

require_once '../db_connection/db_connection.php';

$user_id = (int) $_SESSION['user_id'];
$first_name = 'User';
$email = '';
$last_name = '';
$phone = '';

// Get user details
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $first_name = htmlspecialchars($user['first_name']);
        $last_name = htmlspecialchars($user['last_name']);
        $email = htmlspecialchars($user['email']);
        $phone = htmlspecialchars($user['phone']);
    }
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Handle form submissions
$notification = '';
$notification_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_stmt->execute([$hashed_password, $user_id]);
                        
                        $notification = "Password changed successfully!";
                        $notification_type = "success";
                    } else {
                        $notification = "New password must be at least 8 characters long";
                        $notification_type = "error";
                    }
                } else {
                    $notification = "New passwords do not match";
                    $notification_type = "error";
                }
            } else {
                $notification = "Current password is incorrect";
                $notification_type = "error";
            }
        } catch (PDOException $e) {
            $notification = "Error changing password: " . $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['update_profile'])) {
        $new_first_name = $_POST['first_name'];
        $new_last_name = $_POST['last_name'];
        $new_phone = $_POST['phone'];
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$new_first_name, $new_last_name, $new_phone, $user_id]);
            
            $first_name = htmlspecialchars($new_first_name);
            $last_name = htmlspecialchars($new_last_name);
            $phone = htmlspecialchars($new_phone);
            
            $notification = "Profile updated successfully!";
            $notification_type = "success";
        } catch (PDOException $e) {
            $notification = "Error updating profile: " . $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['delete_account'])) {
        $confirm_password = $_POST['confirm_password_delete'];
        
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($confirm_password, $user['password'])) {
                // Delete user tasks first
                $delete_tasks = $pdo->prepare("DELETE FROM tasks WHERE user_id = ?");
                $delete_tasks->execute([$user_id]);
                
                // Then delete user account
                $delete_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $delete_user->execute([$user_id]);
                
                session_destroy();
                header('Location: ../signin/login.php');
                exit;
            } else {
                $notification = "Password is incorrect";
                $notification_type = "error";
            }
        } catch (PDOException $e) {
            $notification = "Error deleting account: " . $e->getMessage();
            $notification_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Todolist | My Profile</title>
    <link rel="stylesheet" href="../dashboard/style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .profile-section h2 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn {
            background-color: rgba(7, 187, 223, 0.65);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: rgba(5, 150, 180, 0.65);
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
            display: <?= $notification ? 'block' : 'none' ?>;
        }
        
        .notification.error {
            background-color: #e74c3c;
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
        
        .danger-zone {
            border: 1px solid #e74c3c;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .danger-zone h3 {
            color: #e74c3c;
            margin-top: 0;
        }

        /* Calendar Popup Styles */
        .calendar-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 80%;
            max-width: 800px;
            display: none;
        }
        
        .calendar-popup button {
            margin-top: 15px;
            padding: 8px 15px;
            background: rgba(7, 187, 223, 0.65);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
    </style>
</head>
<body>
<!-- Notification -->
<div id="notification" class="notification <?= $notification_type === 'error' ? 'error' : '' ?>">
    <?= $notification ?>
</div>

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
<div id="calendarPopup" class="calendar-popup">
    <h2>Calendar View</h2>
    <div id="calendar"></div>
    <button onclick="closeCalendarPopup()">Close</button>
</div>
<div id="calendarOverlay" class="overlay"></div>

<!-- Popup Sidebar -->
<div id="popupSidebar" class="popup-sidebar" style="display: none;">
    <div class="popup-header">
        <div class="profile-initial" style="background-color:rgba(15, 44, 49, 0.65);"><?= strtoupper(substr($first_name, 0, 1)) ?></div>
        <span class="username" style="color:rgba(8, 56, 65, 0.65)"><?= $first_name; ?></span>
    </div>
    <ul>
        <li><a href="../myprofile/profile.php">My Profile</a></li>
        <li onclick="toggleCalendarPopup()" style="cursor: pointer;">Calendar</li>
        <li><a href="../about/about.php">About</a></li>
        <li><a href="../support/support.php">Support</a></li>
        <li><a href="../dashboard/logout.php">Logout</a></li>
    </ul>
</div>
<div id="overlay" class="overlay" style="display: none;"></div>

<!-- Main Content -->
<div class="main-content">
    <h1>My Profile</h1>
    
    <div class="profile-container">
        <!-- Personal Information Section -->
        <div class="profile-section">
            <h2>Personal Information</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= $first_name ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= $last_name ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= $email ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= $phone ?>">
                </div>
                
                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>
        
        <!-- Privacy & Security Section -->
        <div class="profile-section">
            <h2>Privacy & Security</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
        
        <!-- Danger Zone -->
        <div class="danger-zone">
            <h3>Danger Zone</h3>
            <p>These actions are irreversible. Please proceed with caution.</p>
            
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                <div class="form-group">
                    <label for="confirm_password_delete">Confirm Password to Delete Account</label>
                    <input type="password" id="confirm_password_delete" name="confirm_password_delete" required>
                </div>
                
                <button type="submit" name="delete_account" class="btn btn-danger">Delete My Account</button>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<!-- Your Custom JS -->
<script src="../dashboard/scripts.js"></script>

<script>
    // Initialize calendar variable
    let calendar;
    
    // Toggle popup sidebar
    function togglePopupSidebar() {
        const popup = document.getElementById("popupSidebar");
        const overlay = document.getElementById("overlay");
        popup.style.display = popup.style.display === "block" ? "none" : "block";
        overlay.style.display = overlay.style.display === "block" ? "none" : "block";
    }
    
    // Toggle calendar popup
    function toggleCalendarPopup() {
        const popup = document.getElementById("calendarPopup");
        const overlay = document.getElementById("calendarOverlay");
        
        if (popup.style.display === "block") {
            closeCalendarPopup();
        } else {
            openCalendarPopup();
        }
    }
    
    function openCalendarPopup() {
        const popup = document.getElementById("calendarPopup");
        const overlay = document.getElementById("calendarOverlay");
        
        popup.style.display = "block";
        overlay.style.display = "block";
        
        // Initialize calendar if not already done
        if (!calendar) {
            initializeCalendar();
        } else {
            // Refresh calendar if already initialized
            calendar.render();
        }
    }
    
    function closeCalendarPopup() {
        const popup = document.getElementById("calendarPopup");
        const overlay = document.getElementById("calendarOverlay");
        popup.style.display = "none";
        overlay.style.display = "none";
    }
    
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 400,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch(`../dashboard/task_handler.php?section=calendar&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successCallback(data.tasks);
                        } else {
                            failureCallback(data.message);
                        }
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                alert(`Task: ${info.event.title}\nDue: ${info.event.start.toLocaleString()}`);
            },
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            }
        });
        
        calendar.render();
    }
    
    // Close notification after 3 seconds
    <?php if ($notification): ?>
        setTimeout(() => {
            document.getElementById('notification').style.display = 'none';
        }, 3000);
    <?php endif; ?>
    
    // Close popups when clicking overlay
    document.getElementById('overlay').addEventListener('click', function() {
        document.getElementById('popupSidebar').style.display = 'none';
        this.style.display = 'none';
    });
    
    document.getElementById('calendarOverlay').addEventListener('click', function() {
        closeCalendarPopup();
    });
</script>
</body>
</html>