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

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$Status = array("todo","in-progress","done");

if($_SERVER['REQUEST_METHOD'] === "POST"){
	try {
		$task_id = $_POST['id'];
		$newStatus = $_POST['status'];
		$user_id = $_SESSION['user_id'];
		
		$stmt = $conn->prepare("SELECT user_id FROM tasks WHERE id = ?;");
		$stmt->bind_param("i", $task_id);
		$stmt->execute();
		$result = $stmt->get_result();

		$row = $result->fetch_assoc();

		if (!$row || $row['user_id'] !== $user_id) {
		    echo "INVALID_REQUEST";
		    exit();
		}

		
		$task = new Task($conn);
		
		$message = $task->updateStatus($task_id,$newStatus);
		
		if($message === "SQL_ERROR"){
			$db->closeConnection();
			echo "<script>alert('Internal server error. Redirecting to login...'); window.location.href = 'login.php';</script>";
			exit();
		}

		echo "SUCCESS";
	}
	catch (Throwable $e) {
		echo "SYSTEM_ERROR";
	}
	
	$db->closeConnection();
}
else{	
	$db->closeConnection();
	http_response_code(401);
	echo "SESSION_EXPIRED";
	exit();
}
