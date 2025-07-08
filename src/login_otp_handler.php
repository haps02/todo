<?php

session_start();

$config = require('./config/secret_config.php');
require_once "./classes/Database.php";
require_once "./classes/Auth.php";

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$auth = new Auth($conn);

if($_SERVER['REQUEST_METHOD'] === "POST"){
	$email = $_POST['otp_email'];
	$_SESSION['email'] = $email;

	[$message,$result] = $auth->checkUser($email);

	if($message === "USER_NOT_EXIST"){
		$_SESSION['login_error'] = "User not found!";
		header("Location: login.php");
		exit();
	}
	else if($message === "SQL_ERROR"){
		$_SESSION['login_error'] = "system error!";
		header("Location: login.php");
		exit();
	}
	else{
		$name = $result->fetch_assoc()['name'];

		$result = $auth->sendOtp($email,$name,true);

		if($result){
    		header("Location: enter_otp_login.php");
    		exit();
		}

		$_SESSION['login_error'] = "Error sending OTP. Try again!";
		header("Location: login.php");
		exit();
	}
}