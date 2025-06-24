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
document.addEventListener("click", function(e) {
    const popup = document.getElementById("popupSidebar");
    const profile = document.querySelector(".profile");
    const backdrop = document.getElementById("backdrop");

    if (popup && !popup.contains(e.target) && profile && !profile.contains(e.target)) {
        popup.style.display = "none";
        backdrop.style.display = "none";
    }
});

// Show Add Task Modal
// Show Add Task Modal
function showAddTaskModal(listId, section) {
    const isGroceries = section === 'Groceries';
    
    const modal = document.createElement('div');
    modal.className = 'task-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>${isGroceries ? 'Add New Item' : 'Add New Task'}</h3>
            <div class="form-group">
                <label for="taskDescription">${isGroceries ? 'Item Name' : 'Description'}:</label>
                <input type="text" id="taskDescription" placeholder="${isGroceries ? 'What item to add?' : 'What needs to be done?'}" required>
            </div>
            ${!isGroceries ? `
            <div class="form-group">
                <label for="taskDueDate">Due Date:</label>
                <input type="datetime-local" id="taskDueDate" min="${getCurrentDateTime()}">
            </div>
            <div class="form-group">
                <label for="taskPriority">Priority:</label>
                <select id="taskPriority">
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label for="taskRecurring">Recurring:</label>
                <select id="taskRecurring">
                    <option value="">None</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="weekdays">Weekdays</option>
                </select>
            </div>
            ` : ''}
            <div class="modal-buttons">
                <button onclick="document.body.removeChild(this.parentNode.parentNode.parentNode)">Cancel</button>
                <button onclick="submitTask('${listId}', '${section}')">${isGroceries ? 'Add Item' : 'Add Task'}</button>
            </div>
        </div>
    `;
    
    if (!isGroceries) {
        // Set default due date to today at current time
        const now = new Date();
        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
        modal.querySelector('#taskDueDate').value = localISOTime;
    }
    
    document.body.appendChild(modal);
    modal.querySelector('#taskDescription').focus();
}

// Helper function to get current date-time in the correct format for min attribute
function getCurrentDateTime() {
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    return (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
}
// Submit Task
function submitTask(listId, section) {
    const modal = document.querySelector('.task-modal');
    const description = modal.querySelector('#taskDescription').value.trim();
    const isGroceries = section === 'Groceries';

    if (!description) {
        showNotification(isGroceries ? 'Please enter an item name' : 'Please enter a task description', 'error');
        return;
    }

    const taskData = {
        action: "add",
        description: description,
        section: 'Today', // Always set to Today for the main view
        category: section === 'all' ? 'General' : section, // Use the section as category
        priority: isGroceries ? 'medium' : (modal.querySelector('#taskPriority')?.value || 'medium')
    };

    if (!isGroceries) {
        taskData.due_date = modal.querySelector('#taskDueDate').value;
        taskData.recurring = modal.querySelector('#taskRecurring').value;
    }

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(taskData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.body.removeChild(modal);
            showNotification(isGroceries ? "Item added successfully!" : "Task added successfully!");
            window.location.reload();
            refreshCalendar();
        } else {
            showNotification(data.message || (isGroceries ? "Error adding item" : "Error adding task"), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(isGroceries ? 'Failed to add item. Please try again.' : 'Failed to add task. Please try again.', 'error');
    });
}
// Toggle Task Completion
function toggleTaskCompletion(checkbox, taskId) {
    const isCompleted = checkbox.checked;
    const taskItem = checkbox.closest('li');
    
    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            action: "toggle_complete",
            task_id: taskId,
            is_completed: isCompleted
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            taskItem.classList.toggle('completed', isCompleted);
            showNotification("Task status updated");
            refreshCalendar();
        } else {
            checkbox.checked = !isCompleted; // Revert checkbox state
            showNotification(data.message || "Failed to update task status", 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !isCompleted; // Revert checkbox state
        showNotification("Failed to update task status. Please try again.", 'error');
    });
}

// Edit Task
// Edit Task
function editTask(taskId) {
    // First fetch the task details
    fetch(`task_handler.php?task_id=${taskId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.tasks.length > 0) {
                const task = data.tasks[0];
                const isGroceries = task.category === 'Groceries';
                
                // Create edit modal
                const modal = document.createElement('div');
                modal.className = 'task-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Edit ${isGroceries ? 'Item' : 'Task'}</h3>
                        <div class="form-group">
                            <label for="editDescription">${isGroceries ? 'Item Name' : 'Description'}:</label>
                            <input type="text" id="editDescription" value="${escapeHtml(task.description)}" required>
                        </div>
                        ${!isGroceries ? `
                        <div class="form-group">
                            <label for="editDueDate">Due Date:</label>
                            <input type="datetime-local" id="editDueDate" value="${task.due_date ? formatDateForInput(task.due_date) : ''}" min="${getCurrentDateTime()}">
                        </div>
                        <div class="form-group">
                            <label for="editPriority">Priority:</label>
                            <select id="editPriority">
                                <option value="low" ${task.priority === 'low' ? 'selected' : ''}>Low</option>
                                <option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Medium</option>
                                <option value="high" ${task.priority === 'high' ? 'selected' : ''}>High</option>
                            </select>
                        </div>
                        ` : ''}
                        <div class="modal-buttons">
                            <button onclick="document.body.removeChild(this.parentNode.parentNode.parentNode)">Cancel</button>
                            <button onclick="saveTaskChanges(${taskId}, ${isGroceries ? 'true' : 'false'})">Save Changes</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                modal.querySelector('#editDescription').focus();
            } else {
                showNotification("Failed to load task details", 'error');
            }
        });
}


// Save edited task
function saveTaskChanges(taskId, isGroceries) {
    const modal = document.querySelector('.task-modal');
    const description = modal.querySelector('#editDescription').value.trim();
    let taskData = {
        action: "edit",
        task_id: taskId,
        description: description
    };

    if (!isGroceries) {
        taskData.due_date = modal.querySelector('#editDueDate').value;
        taskData.priority = modal.querySelector('#editPriority').value;
    }

    if (!description) {
        showNotification(isGroceries ? 'Please enter an item name' : 'Please enter a task description', 'error');
        return;
    }

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(taskData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.body.removeChild(modal);
            showNotification(isGroceries ? "Item updated successfully!" : "Task updated successfully!");
            window.location.reload();
            refreshCalendar();
        } else {
            showNotification(data.message || (isGroceries ? "Error updating item" : "Error updating task"), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(isGroceries ? 'Failed to update item. Please try again.' : 'Failed to update task. Please try again.', 'error');
    });
}
function deleteAllCheckedTasks() {
    const checkedTasks = document.querySelectorAll('#tasksList input[type="checkbox"]:checked');
    if (checkedTasks.length === 0) {
        showNotification('No tasks are checked for deletion', 'error');
        return;
    }

    if (!confirm(`Are you sure you want to delete ${checkedTasks.length} checked tasks?`)) {
        return;
    }

    const taskIds = Array.from(checkedTasks).map(checkbox => 
        parseInt(checkbox.closest('li').dataset.taskId)
    );

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            action: "delete_multiple",
            task_ids: taskIds
        })
    })
    .then(response => {
        // First check if the response is OK (status 200-299)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Even if the response is OK, check the success flag
        if (data && data.success !== false) {
            // Remove all checked tasks from the UI
            checkedTasks.forEach(checkbox => {
                const taskItem = checkbox.closest('li');
                taskItem.classList.add('deleting');
                setTimeout(() => taskItem.remove(), 300);
            });
            
            showNotification(`Deleted ${checkedTasks.length} tasks successfully!`);
            refreshCalendar();
            
            // Show "no tasks" message if all tasks were deleted
            const taskList = document.querySelector('#tasksList ul');
            if (taskList && taskList.children.length === checkedTasks.length) {
                const noTasksMsg = document.createElement('li');
                noTasksMsg.className = 'no-tasks';
                noTasksMsg.textContent = 'No tasks found. Click + Add task to get started!';
                taskList.appendChild(noTasksMsg);
            }
        } else {
            // Handle case where server responds with success=false
            showNotification(data.message || "Failed to delete tasks", 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        // Check if the error might be a successful deletion despite the error
        // We'll assume it worked and remove the tasks anyway
        checkedTasks.forEach(checkbox => {
            const taskItem = checkbox.closest('li');
            taskItem.classList.add('deleting');
            setTimeout(() => taskItem.remove(), 300);
        });
        
        showNotification('Tasks removed. Refreshing may be needed.', 'info');
        refreshCalendar();
    });
}
// Delete Task
function deleteTask(taskId, btn) {
    if (!confirm("Are you sure you want to delete this task?")) return;

    fetch("task_handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ 
            action: "delete", 
            task_id: taskId 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const taskItem = btn.closest('li');
            taskItem.classList.add('deleting');
            
            setTimeout(() => {
                taskItem.remove();
                
                // If this was the last task, show "no tasks" message
                const list = btn.closest('ul');
                if (list && list.children.length === 0) {
                    const noTasksMsg = document.createElement('li');
                    noTasksMsg.className = 'no-tasks';
                    noTasksMsg.textContent = 'No tasks found. Click + Add task to get started!';
                    list.appendChild(noTasksMsg);
                }
            }, 300);
            showNotification("Task deleted successfully!");
            refreshCalendar();
        } else {
            showNotification(data.message || "Failed to delete task", 'error');
        }
    });
}

// Search Tasks
function searchTasks(keyword) {
    if (keyword.length < 2) {
        document.querySelectorAll('.search-result').forEach(el => el.remove());
        return;
    }

    fetch("search.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `keyword=${encodeURIComponent(keyword)}`
    })
    .then(res => res.text())
    .then(html => {
        document.querySelectorAll('.search-result').forEach(el => el.remove());
        
        let resultsContainer = document.querySelector('#searchResults');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'searchResults';
            resultsContainer.className = 'search-results';
            document.querySelector('.main-content').prepend(resultsContainer);
        }
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        Array.from(tempDiv.children).forEach(result => {
            result.className = 'search-result';
            resultsContainer.appendChild(result);
        });
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

// Helper function to format date for input field
function formatDateForInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const timezoneOffset = date.getTimezoneOffset() * 60000;
    return (new Date(date - timezoneOffset)).toISOString().slice(0, 16);
}

// Helper function to format date for display
function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for the backdrop
    const backdrop = document.getElementById('backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            document.getElementById('popupSidebar').style.display = 'none';
            this.style.display = 'none';
        });
    }
});

// Make functions available globally
window.togglePopupSidebar = togglePopupSidebar;
window.toggleCalendarPopup = toggleCalendarPopup;
window.showAddTaskModal = showAddTaskModal;
window.submitTask = submitTask;
window.toggleTaskCompletion = toggleTaskCompletion;
window.editTask = editTask;
window.saveTaskChanges = saveTaskChanges;
window.deleteTask = deleteTask;
window.searchTasks = searchTasks;
window.refreshCalendar = refreshCalendar;
window.filterByCategory = filterByCategory;
window.filterByPriority = filterByPriority;
window.showNotification = showNotification;



function togglePopupSidebar() {
    const sidebar = document.getElementById('popupSidebar');
    const overlay = document.getElementById('overlay');
  
    const isOpen = sidebar.style.display === 'block';
  
    sidebar.style.display = isOpen ? 'none' : 'block';
    overlay.style.display = isOpen ? 'none' : 'block';
  }
  
  // Optional: Close sidebar when clicking the overlay
  document.getElementById('overlay').addEventListener('click', togglePopupSidebar);
  
  
  
  
      function getColorFromName($name) {
          $colors = ['#4CAF50', '#2196F3', '#9C27B0', '#FF9800', '#E91E63', '#009688'];
          $hash = crc32($name) % count($colors);
          return $colors[$hash];
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
                  fetch(`task_handler.php?section=calendar&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
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
  
      // Filter tasks by category
      function filterByCategory(category) {
          window.location.href = `dashboard.php?category=${encodeURIComponent(category)}`;
      }
  
      // Filter tasks by priority
      function filterByPriority(priority) {
          window.location.href = `dashboard.php?priority=${encodeURIComponent(priority)}`;
      }
  
      // Set task priority
      function setPriority(select, taskId) {
          const priority = select.value;
          const taskItem = select.closest('li');
          const description = taskItem.querySelector('.desc').textContent;
          const category = taskItem.dataset.category || 'General';
          const dueDate = taskItem.querySelector('.due-date')?.dataset.fullDate || null;
  
          fetch("task_handler.php", {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
              },
              body: JSON.stringify({
                  action: "edit",
                  task_id: taskId,
                  description: description,
                  priority: priority,
                  category: category,
                  due_date: dueDate
              })
          })
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  showNotification("Priority updated successfully!");
                  taskItem.classList.remove('priority-low', 'priority-medium', 'priority-high');
                  taskItem.classList.add(`priority-${priority}`);
                  refreshCalendar();
              } else {
                  showNotification(data.message || "Failed to update priority", 'error');
                  select.value = taskItem.classList.contains('priority-high') ? 'high' : 
                                taskItem.classList.contains('priority-medium') ? 'medium' : 'low';
              }
          })
          .catch(error => {
              console.error('Error:', error);
              showNotification('Failed to update priority', 'error');
              select.value = taskItem.classList.contains('priority-high') ? 'high' : 
                            taskItem.classList.contains('priority-medium') ? 'medium' : 'low';
          });
      }
  
      
  
      // Add suggested task
      function addSuggestedTask(description) {
          const currentCategory = new URLSearchParams(window.location.search).get('category') || 'General';
          
          fetch("task_handler.php", {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
              },
              body: JSON.stringify({
                  action: "add",
                  description: description,
                  section: "Today",
                  category: currentCategory,
                  priority: "medium"
              })
          })
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  showNotification("Task added successfully!");
                  window.location.reload();
              } else {
                  showNotification(data.message || "Error adding task", 'error');
              }
          })
          .catch(error => {
              console.error('Error:', error);
              showNotification('Failed to add task. Please try again.', 'error');
          });
      }
  
      // Show notification
      function showNotification(message, type = 'success') {
          const notification = document.getElementById('notification');
          notification.textContent = message;
          notification.className = `notification ${type === 'error' ? 'error' : ''}`;
          notification.style.display = 'block';
          
          setTimeout(() => {
              notification.style.display = 'none';
          }, 3000);
      }
  
      // Helper function to escape HTML
      function escapeHtml(unsafe) {
          return unsafe
              .replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
      }
  
      // Refresh calendar after task operations
      function refreshCalendar() {
          if (calendar) {
              calendar.refetchEvents();
          }
      }