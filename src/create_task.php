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

if(!isset($_SESSION['user_id'])){
	header("Location: login.php");
	exit();
}

if($_SERVER['REQUEST_METHOD'] === "POST"){
	$title = $_POST['title'];
	$description = $_POST['description'];
	$user_id = $_SESSION['user_id'];
	
	$task = new Task($conn);
	
	$result = $task->createTask($title,$description,$user_id);
	
	if($result === "SQL_ERROR"){
		echo "Internal server error!";
	}
	
	echo "Task created successfully!";
}
else{
	echo "Invalid request!";
}

$db->closeConnection();
