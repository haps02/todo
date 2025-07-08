<?php

class Task{
	private $conn;
	
	public function __construct($conn){
		$this->conn = $conn;
	}
	
	public function createTask($title, $description, $user_id){
		try {	
			$created_at = date('Y-m-d');
			$updated_at = $created_at;
			$status = 0;
			$stmt = $this->conn->prepare("INSERT INTO tasks (user_id, title, description, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?);");
			$stmt->bind_param("ississ",$user_id,$title,$description,$status,$created_at,$updated_at);
			$stmt->execute();
			
			return "SUCCESS";
		}
		catch(mysqli_sql_exception $e) {
			return "SQL_ERROR";
		}
	}
	
	public function updateStatus($id,$status){
		try {	
			$stmt = $this->conn->prepare("SELECT status FROM tasks WHERE id=?;");
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$result = $stmt->get_result();
			$row = $result->fetch_assoc();
			$oldStatus = $row['status'];
			if($oldStatus != $status){
				$stmt = $this->conn->prepare("INSERT INTO task_history (old_status,new_status,task_id) VALUES (?,?,?);");
				$stmt->bind_param("iii",$oldStatus,$status,$id);
				$stmt->execute();
			}
			
			
			$updated_at = date('Y-m-d');
			$resolved_at = null;
			if($status == 2){
				$resolved_at = $updated_at;
			}
			$stmt = $this->conn->prepare("UPDATE tasks SET status=?,updated_at=?,resolved_at=? WHERE id=?;");
			$stmt->bind_param("issi",$status,$updated_at,$resolved_at,$id);
			$stmt->execute();
			
			return "SUCCESS";
		}
		catch(mysqli_sql_exception $e) {
			return "SQL_ERROR";
		}
	}
	
	public function getHistory($task_id){
		try {
			$stmt = $this->conn->prepare("SELECT old_status,new_status,changed_at FROM task_history WHERE task_id=?;");
			$stmt->bind_param("i",$task_id);
			$stmt->execute();
			$result = $stmt->get_result();
			
			return ["SUCCESS",$result];
		}
		catch(mysqli_sql_exception $e){
			return ["SQL_ERROR",""];
		}
	}
	
	public function getAllTasks($user_id, $search='', $dateFilter='', $statusFilter=''){
		try {	
			$query = "SELECT * FROM tasks WHERE user_id = ?";
			$params = ["i", $user_id];

			if ($search) {
				$query .= " AND title LIKE ?";
				$params[0] .= "s";
				$params[] = $search . '%';
			}

			if ($statusFilter !== '') {
				$query .= " AND status = ?";
				$params[0] .= "i";
				$params[] = (int)$statusFilter;
			}

			if ($dateFilter !== '') {
				$today = date('Y-m-d');
				switch ($dateFilter) {
					case 'today':
						$query .= " AND created_at = ?";
						$params[0] .= "s";
						$params[] = $today;
						break;
					case 'yesterday':
						$query .= " AND created_at = ?";
						$params[0] .= "s";
						$params[] = date('Y-m-d', strtotime('-1 day'));
						break;
					case 'last_week':
						$query .= " AND created_at >= ?";
						$params[0] .= "s";
						$params[] = date('Y-m-d', strtotime('-7 days'));
						break;
					case 'last_month':
						$query .= " AND created_at >= ?";
						$params[0] .= "s";
						$params[] = date('Y-m-d', strtotime('-1 month'));
						break;
					case 'last_year':
						$query .= " AND created_at >= ?";
						$params[0] .= "s";
						$params[] = date('Y-m-d', strtotime('-1 year'));
						break;
				}
			}

			$query .= " ORDER BY created_at DESC;";
			$stmt = $this->conn->prepare($query);
			$stmt->bind_param(...$params);
			$stmt->execute();
			$result = $stmt->get_result();
			
			return ["SUCCESS",$result];
		}
		catch(mysqli_sql_exception $e) {
			return ["SQL_ERROR",NULL];
		}
	}
}	
