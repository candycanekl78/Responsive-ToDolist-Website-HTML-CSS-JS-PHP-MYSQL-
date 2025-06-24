# Responsive-ToDolist-Website-HTML-CSS-JS-PHP-MYSQL-


To-Do List App (HTML, CSS, JavaScript, PHP, MySQL)
A simple and responsive To-Do List Web Application that allows users to create, manage, and delete tasks in real-time. Built using HTML, CSS, JavaScript, PHP, and MySQL, this project demonstrates full-stack functionality with database integration.




[Uploading Screenshot 2025-05-04 at 4.49.58 PM.png…]()

🔧 Features
-----------------------------
Add, update, delete tasks

Mark tasks as completed

Task list stored in MySQL database

Simple and responsive UI

Real-time updates using JavaScript

Backend powered by PHP


📋 System Requirements
------------------------
PHP 7.x or higher

MySQL 5.6 or higher

Apache Server (XAMPP, WAMP, or similar)

Web Browser (Chrome, Firefox, etc.)

Code Editor (VS Code recommended)


🛠️ Setup Instruction
-------------------------------------
Clone or Download the Repository

bash
Copy
Edit
git clone https://github.com/candycanekl78/to-do-list-app.git
Start Apache & MySQL
Open XAMPP or WAMP and ensure both Apache and MySQL are running.

Import the Database

Open phpMyAdmin

Create a new database, e.g., todo_db

Import the provided todo.sql file

Edit db.php with your credentials

php
Copy
Edit
<?php
$host = 'localhost';
$user = 'root';       // default XAMPP user
$pass = '';           // default is empty
$db   = 'todo_db';    // database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
Place the project folder in your server directory

For XAMPP: C:/xampp/htdocs/to-do-list-app/

For WAMP: C:/wamp/www/to-do-list-app/

Open the App in Browser

pgsql
Copy
Edit
http://localhost/to-do-list-app/index.html
🧪 Sample SQL (todo.sql)
sql
Copy
Edit
CREATE DATABASE IF NOT EXISTS todo_db;
USE todo_db;

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task VARCHAR(255) NOT NULL,
  is_completed BOOLEAN DEFAULT FALSE
);


🙌 Contributing
----------------------------
Feel free to fork the repo, improve the features, and create a pull request. Suggestions and improvements are welcome!

🧑‍💻 Author
---------------------------
Shamil P
Full Stack Developer | UI/UX Designer | Cybersecurity Enthusiast

