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

// Handle support form submission
$notification = '';
$notification_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_support'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $email = $_POST['email'];
    
    if (!empty($subject) && !empty($message) && !empty($email)) {
        // In a real application, you would send an email or save to database
        $notification = "Thank you for contacting support! We'll get back to you soon.";
        $notification_type = "success";
    } else {
        $notification = "Please fill in all required fields.";
        $notification_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Todolist | Support</title>
    <link rel="stylesheet" href="../dashboard/style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        .support-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .support-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .support-header h1 {
            color: rgba(7, 187, 223, 0.65);
            margin-bottom: 10px;
        }
        
        .support-form .form-group {
            margin-bottom: 20px;
        }
        
        .support-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .support-form input,
        .support-form select,
        .support-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .support-form textarea {
            height: 150px;
            resize: vertical;
        }
        
        .btn {
            background-color: rgba(7, 187, 223, 0.65);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: rgba(5, 150, 180, 0.65);
        }
        
        .faq-section {
            margin-top: 40px;
        }
        
        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .faq-question {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        .faq-answer {
            color: #555;
            line-height: 1.6;
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
<div id="calendarPopup" class="calendar-popup" style="display: none;">
    <h2>Calendar View</h2>
    <div id="calendar"></div>
    <button onclick="toggleCalendarPopup()">Close</button>
</div>

<!-- Popup Sidebar -->
<div id="popupSidebar" class="popup-sidebar" style="display: none;">
    <div class="popup-header">
        <div class="profile-initial" style="background-color:rgba(15, 44, 49, 0.65);"><?= strtoupper(substr($first_name, 0, 1)) ?></div>
        <span class="username" style="color:rgba(8, 56, 65, 0.65)"><?= $first_name; ?></span>
    </div>
    <ul>
        <li><a href="../myprofile/profile.php">My Profile</a></li>
        <li onclick="toggleCalendarPopup()">Calendar</li>
        <li><a href="../about/about.php">About</a></li>
        <li><a href="../support/support.php">Support</a></li>
        <li><a href="../dashboard/logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="support-container">
        <div class="support-header">
            <h1>Support Center</h1>
            <p>We're here to help you with any questions or issues</p>
        </div>
        
        <div class="support-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" name="submit_support" class="btn">Send Message</button>
            </form>
        </div>
        
        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <div class="faq-question">How do I create a new task?</div>
                <div class="faq-answer">
                    Click the "+ Add Task" button on your dashboard. Fill in the task details including description, due date, and priority level, then click "Save".
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Can I change a task's priority after creating it?</div>
                <div class="faq-answer">
                    Yes, you can change a task's priority at any time by selecting a different option from the priority dropdown next to the task.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How do I view my tasks in calendar format?</div>
                <div class="faq-answer">
                    Click on "Calendar" in the sidebar to open the calendar view. Your tasks with due dates will appear on their respective dates.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Is my data secure?</div>
                <div class="faq-answer">
                    Yes, we take security seriously. Your data is encrypted and only accessible with your account credentials.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How can I change my password?</div>
                <div class="faq-answer">
                    Go to "My Profile" from the popup sidebar, then navigate to the "Privacy & Security" section to change your password.
                </div>
            </div>
        </div>
        
        <div class="contact-info" style="margin-top: 30px;">
            <h3>Additional Support</h3>
            <p>If you need immediate assistance, please email us at: <strong>support@todolistapp.com</strong></p>
            <p>Our support team is available Monday-Friday, 9am-5pm EST.</p>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<!-- Your Custom JS -->
<script src="../dashboard/scripts.js"></script>

<script>
    // Toggle popup sidebar
    function togglePopupSidebar() {
        const popup = document.getElementById("popupSidebar");
        popup.style.display = popup.style.display === "block" ? "none" : "block";
    }
    
    // Toggle calendar popup
    function toggleCalendarPopup() {
        const popup = document.getElementById("calendarPopup");
        const isVisible = popup.style.display === "block";

        popup.style.display = isVisible ? "none" : "block";

        if (!isVisible && calendar) {
            setTimeout(() => {
                calendar.render();
            }, 10);
        }
    }
    
    // Initialize FullCalendar
    let calendar;
    document.addEventListener('DOMContentLoaded', function() {
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
    });
    
    // Toggle FAQ answers
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            answer.style.display = answer.style.display === 'none' ? 'block' : 'none';
        });
    });
    
    // Close notification after 3 seconds
    <?php if ($notification): ?>
        setTimeout(() => {
            document.getElementById('notification').style.display = 'none';
        }, 3000);
    <?php endif; ?>
</script>
</body>
</html>