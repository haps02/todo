<?php
function renderTaskCard($id, $title, $description, $status, $created_at, $updated_at, $resolved_at) {
  $safeTitle = htmlspecialchars($title);
  $safeDesc = htmlspecialchars($description);
  $safeStatus = htmlspecialchars($status);
  $safeCreated = htmlspecialchars($created_at);
  $safeUpdated = htmlspecialchars($updated_at);
  $safeResolved = $resolved_at ? htmlspecialchars($resolved_at) : "<span class='text-danger'>Not resolved</span>";

  // Determine badge class
  $badge = match (strtolower($status)) {
    "done" => "success",
    "in-progress" => "warning",
    default => "secondary"
  };

  // Helper to mark selected option
  $isSelected = fn($actual, $expected) =>
    strtolower($actual) === strtolower($expected) ? "selected" : "";

  echo <<<HTML
  <div class="border rounded p-3 mb-3 bg-white shadow-sm d-flex justify-content-between align-items-start flex-wrap">
    <div class="flex-grow-1 me-3">
      <h5 class="mb-1">$safeTitle</h5>
      <p class="mb-1">$safeDesc</p>
      <span class="badge bg-$badge text-uppercase">$safeStatus</span>

      <div class="mt-3">
        <label class="form-label">Change Status:</label>
        <select class="form-select" onchange="updateStatus($id, this.value)">
          <option value="0" {$isSelected($status, 'todo')}>To Do</option>
          <option value="1" {$isSelected($status, 'in-progress')}>In Progress</option>
          <option value="2" {$isSelected($status, 'done')}>Done</option>
        </select>
        
        <button class="btn btn-outline-info btn-sm mt-3" onclick="showHistory($id)">
          <i class="fas fa-history"></i> History
        </button>
      </div>
    </div>

    <div class="text-end small text-muted">
      <div><strong>Created:</strong> $safeCreated</div>
      <div><strong>Updated:</strong> $safeUpdated</div>
      <div><strong>Resolved:</strong> $safeResolved</div>
    </div>
  </div>
  HTML;
}
?>

