<?php
$pageTitle = "Dashboard";
require_once "components/header.php";
require_once "./classes/Database.php";
require_once "./classes/Task.php";
?>

<!-- Task Form -->
<div class="card shadow mb-4">
  <div class="card-header bg-primary text-white">Add New Task</div>
  <div class="card-body">
    <form id="taskForm">
      <div class="form-outline mb-3">
        <input type="text" name="title" class="form-control" maxlength="100" required />
        <label class="form-label">Title</label>
      </div>
      <div class="form-outline mb-3">
        <textarea name="description" class="form-control" rows="3" maxlength="100" required></textarea>
        <label class="form-label">Description</label>
      </div>
      <button type="submit" class="btn btn-primary">Add Task</button>
    </form>
  </div>
</div>

<!-- Search and Filter Bar -->
<div class="d-flex justify-content-between mb-3">
  <input type="text" id="searchInput" class="form-control w-75" placeholder="Search tasks by title..." />
  <button class="btn btn-outline-secondary ms-2" data-mdb-toggle="modal" data-mdb-target="#filterModal">
    <i class="fas fa-filter"></i> Filter
  </button>
</div>

<!-- Task List -->
<div class="card shadow">
  <div class="card-header bg-secondary text-white">Your Tasks</div>
  <div class="card-body" id="taskList"></div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel">Filter Tasks</h5>
        <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="filterForm">
          <h6>By Date</h6>
          <select class="form-select mb-3" name="date_filter">
            <option value="">-- None --</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_week">Last Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_year">Last Year</option>
          </select>

          <h6>By Status</h6>
          <select class="form-select mb-3" name="status_filter">
            <option value="">-- Any --</option>
            <option value="0">To Do</option>
            <option value="1">In Progress</option>
            <option value="2">Done</option>
          </select>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="applyFilters()" data-mdb-dismiss="modal">Apply</button>
      </div>
    </div>
  </div>
</div>

<!-- Task History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyModalLabel">Task Status History</h5>
        <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="historyContent">
        <!-- History content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<script>
function updateStatus(taskId, newStatus) {
  const formData = new FormData();
  formData.append("id", taskId);
  formData.append("status", newStatus);

  fetch("change_status.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(msg => {
    if (msg === "SUCCESS") {
      loadTasks();
    }
    else if(msg === "NOT_PAID"){
      alert("You must complete the payment to change task status.");
      window.location.href = "payment.php";
    }
    else {
      alert("Failed to update status.");
    }
  })
  .catch(() => {
    alert("Error occurred while updating status.");
  });
}

function showHistory(taskId) {
  const formData = new FormData();
  formData.append("task_id", taskId);

  fetch("get_history.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.text())
    .then(html => {
      if (html.includes("INVALID_REQUEST") || html.includes("SESSION_EXPIRED")) {
        alert("Not authorized or session expired.");
        window.location.href = "login.php";
        return;
      }
      else if(html.includes("NOT_PAID")){
        document.getElementById("taskList").innerHTML = `
          <div class="alert alert-warning text-center">
            <strong>Payment Required:</strong> Please pay the subscription fee to access your tasks.
            <br />
            <a href="payment.php" class="btn btn-sm btn-warning mt-2">Pay Now</a>
          </div>
        `;
        return;
      }
      document.getElementById("historyContent").innerHTML = html;
      const modal = new mdb.Modal(document.getElementById('historyModal'));
      modal.show();
    })
    .catch(() => {
      document.getElementById("historyContent").innerHTML = "<p>Error loading history.</p>";
    });
}

function loadTasks(query = "", filters = {}) {
  const formData = new FormData();
  formData.append("search", query);
  if (filters.date_filter) formData.append("date_filter", filters.date_filter);
  if (filters.status_filter) formData.append("status_filter", filters.status_filter);

  fetch("get_tasks.php", { method: "POST", body: formData })
    .then(res => res.text())
    .then(html => {
      if (html.includes("SESSION_EXPIRED")) {
        alert("Session expired! Redirecting to login...");
        window.location.href = "login.php";
        return;
      }
      else if(html.includes("NOT_PAID")){
        document.getElementById("taskList").innerHTML = `
          <div class="alert alert-warning text-center">
            <strong>Payment Required:</strong> Please pay the subscription fee to access your tasks.
            <br />
            <a href="payment.php" class="btn btn-sm btn-warning mt-2">Pay Now</a>
          </div>
        `;
        return;
      }
      document.getElementById("taskList").innerHTML = html;
    });
}

function applyFilters() {
  const form = document.getElementById("filterForm");
  const date_filter = form.date_filter.value;
  const status_filter = form.status_filter.value;
  const query = document.getElementById("searchInput").value.trim();
  loadTasks(query, { date_filter, status_filter });
}

document.getElementById("taskForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch("create_task.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(msg => {
    if(msg === "NOT_PAID"){
      alert("You must complete the payment to change task status.");
      // window.location.href = "payment.php";
      // return;
    }
    else{
      alert(msg);
      loadTasks();
      this.reset();
    }
  });
});

document.getElementById("searchInput").addEventListener("input", function () {
  const query = this.value.trim();
  loadTasks(query);
});

loadTasks();
</script>

<?php require_once "components/footer.php"; ?>

