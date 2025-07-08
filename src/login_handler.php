<?php

$config = require('./config/secret_config.php');
require_once "./classes/Auth.php";
require_once "./classes/Database.php";

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$auth = new Auth($conn);

session_start();

if($_SERVER['REQUEST_METHOD'] === "POST"){
	$email = $_POST['email'];
	$pass = $_POST['pass'];
	
	$result = $auth->login($email,$pass);
	
	if($result === "WRONG_PASSWORD"){
		$_SESSION['login_error'] = "Wrong Password!";
		header("Location: login.php");
	}
	else if($result === "USER_NOT_EXIST"){
		$_SESSION['login_error'] = "User not found!";
		header("Location: login.php");
	}
	else if($result === "SQL_ERROR"){
		$_SESSION['login_error'] = "system error!";
		header("Location: login.php");
	}
	else{
	
		$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?;");
		$stmt->bind_param("i",$result);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$user = $result->fetch_assoc();
		
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['email'] = $user['email'];
		$_SESSION['name'] = $user['name'];
		$_SESSION['is_paid'] = $user['is_paid'];
		
		header("Location: dashboard.php");
	}
}
else{
	$_SESSION['login_error'] = "Internal Sever Error.";
	header("Location: login.php");
}

$db->closeConnection();
