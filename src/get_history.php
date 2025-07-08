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
	$task_id = $_POST['task_id'];
        $user_id = $_SESSION['user_id'];

        // $db = new Database("127.0.0.1", "root", "rootpass", "my_db");
        // $conn = $db->getConnection();

    	// Verify ownership
        $stmt = $conn->prepare("SELECT user_id FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if (!$res || $res['user_id'] !== $user_id) {
            echo "INVALID_REQUEST";
            exit();
        }
        
        $task = new Task($conn);
        [$message,$result] = $task->getHistory($task_id);
        
        if($message == "SUCCESS"){
        	while ($row = $result->fetch_assoc()) {
        		echo "<p>{$row['changed_at']}: <strong>{$Status[$row['old_status']]}</strong> → <strong>{$Status[$row['new_status']]}</strong></p>";
    		}	
        }
        else{
        	echo "SERVER_ERROR";
        }
	
	$db->closeConnection();
}
else{	
	$db->closeConnection();
	http_response_code(401);
	echo "SESSION_EXPIRED";
	exit();
}
