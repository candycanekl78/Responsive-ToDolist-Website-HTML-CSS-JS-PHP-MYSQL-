// Popup Sidebar Toggle
function togglePopupSidebar() {
    const popup = document.getElementById("popupSidebar");
    const backdrop = document.getElementById("backdrop");

    const isVisible = popup.style.display === "block";
    popup.style.display = isVisible ? "none" : "block";
    backdrop.style.display = isVisible ? "none" : "block";
}

// Calendar Popup Toggle
function toggleCalendarPopup() {
    const calendar = document.getElementById("calendarPopup");
    calendar.style.display = calendar.style.display === "block" ? "none" : "block";
}

// Hide popup if clicked outside
document.addEventListener("click", function (e) {
    const popup = document.getElementById("popupSidebar");
    const profile = document.querySelector(".profile");
    const backdrop = document.getElementById("backdrop");

    if (!popup.contains(e.target) && !profile.contains(e.target)) {
        popup.style.display = "none";
        backdrop.style.display = "none";
    }
});

// Load Tasks Dynamically
function loadTasks() {
    fetch("task_handler.php")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const sections = {
                    "My Projects": document.getElementById("projectsList"),
                    "Upcoming": document.getElementById("teamList")
                };

                Object.values(sections).forEach(ul => ul.innerHTML = "");

                if (data.tasks.length === 0) {
                    Object.values(sections).forEach(ul => {
                        ul.innerHTML = "<li>No tasks found</li>";
                    });
                    return;
                }

                data.tasks.forEach(task => {
                    const ul = sections[task.section] || sections["My Projects"];
                    const li = document.createElement("li");

                    const timeHTML = task.time ? `<span class="time">${task.date} ${task.time}</span>` : "";

                    li.innerHTML = `
                        <input type="checkbox" onchange="toggleTask(this)" />
                        <span class="desc">${task.description}</span>
                        ${timeHTML}
                        <button class="edit-btn" onclick="editTask(this, ${task.id})">Edit</button>
                        <button class="dlt-btn" onclick="deleteTask(${task.id}, this)" style="display: none;">Delete</button>
                    `;
                    ul.appendChild(li);
                });
            } else {
                alert("Failed to load tasks.");
            }
        });
}

// Add Task
function addTask(listId) {
    const section = listId === 'projectsList' ? 'My Projects' : 'Upcoming';
    const description = prompt("Enter task description:");
    if (!description) return;

    let time = "";
    let date = "";

    if (section === "Upcoming") {
        date = prompt("Enter task date (YYYY-MM-DD):");
        if (!date) return;

        time = prompt("Enter task time (e.g., 15:30):");
        if (!time) return;
    }

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            action: "add",
            description: description,
            time: time,
            date: date,
            section: section
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        } else {
            alert(data.message || "Error adding task.");
        }
    });
}

// Delete Task
function deleteTask(id, btn) {
    if (!confirm("Are you sure you want to delete this task?")) return;

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ action: "delete", task_id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.parentElement.remove();
        } else {
            alert(data.message || "Failed to delete task.");
        }
    });
}

// Toggle strikethrough and show delete button
function toggleTask(checkbox) {
    const desc = checkbox.nextElementSibling;
    desc.style.textDecoration = checkbox.checked ? "line-through" : "none";

    const deleteBtn = checkbox.parentElement.querySelector(".dlt-btn");
    if (deleteBtn) {
        deleteBtn.style.display = checkbox.checked ? "inline-block" : "none";
    }
}

// Edit Task
function editTask(btn, id) {
    const li = btn.parentElement;
    const desc = li.querySelector(".desc");

    if (!desc.isContentEditable) {
        desc.contentEditable = true;
        desc.focus();
        btn.textContent = "Save";
    } else {
        desc.contentEditable = false;
        btn.textContent = "Edit";

        fetch("task_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ action: "edit", task_id: id, description: desc.textContent.trim() })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || "Failed to update task.");
            }
        });
    }
}

// Initial load
window.addEventListener("DOMContentLoaded", loadTasks);
