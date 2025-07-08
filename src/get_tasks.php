<?php

session_start();

if(!isset($_SESSION['user_id'])){
	header("Location: login.php");
	exit();
}

if(!isset($_SESSION['is_paid']) || $_SESSION['is_paid'] != 1){
    echo "NOT_PAID";
    exit();
}

$config = require('./config/secret_config.php');
require_once "./classes/Task.php";
require_once "./classes/Database.php";
require_once "components/task_card.php";

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$Status = array("todo","in-progress","done");

if($_SERVER['REQUEST_METHOD'] === "POST"){
	$user_id = $_SESSION['user_id'];
	$search = $_POST['search'] ?? '';
	$dateFilter = $_POST['date_filter'] ?? '';
	$statusFilter = $_POST['status_filter'] ?? '';
	
	$task = new Task($conn);
	
	[$message,$result] = $task->getAllTasks($user_id,$search,$dateFilter,$statusFilter);
	
	if($message === "SQL_ERROR"){
		$db->closeConnection();
		echo "<script>alert('Internal server error. Redirecting to login...'); window.location.href = 'login.php';</script>";
		exit();
	}

	while ($row = $result->fetch_assoc()) {
	    renderTaskCard($row['id'],$row['title'], $row['description'], $Status[$row['status']], $row['created_at'], $row['updated_at'], $row['resolved_at']);
	}
	
	$db->closeConnection();
}
else{	
	$db->closeConnection();
	http_response_code(401);
	echo "SESSION_EXPIRED";
	exit();
}
