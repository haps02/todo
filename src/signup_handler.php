<?php
session_start();

$config = require('./config/secret_config.php');
require_once "./classes/Database.php";
require_once "./classes/Auth.php";

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$auth = new Auth($conn);


if($_SERVER['REQUEST_METHOD'] === "POST"){
	$name = $_POST['name'];
	$email = $_POST['email'];
	$pass = $_POST['pass'];
	
	$nameLen = strlen($name);
	$emailLen = strlen($email);
	$passLen = strlen($pass);
	
	if($nameLen>100 or $emailLen>100 or $passLen>100){
		$_SESSION['signup_error'] = "Name, Email and Password should be less than 100 characters.";
       	header("Location: signup.php");
		exit();
	}

	[$message,$result] = $auth->checkUser($email);

	if($message === "USER_EXIST"){
		$_SESSION['signup_error'] = "User already exist! Try to login";
		header("Location: signup.php");
		exit();
	}
	else if($message === "SQL_ERROR"){
		$_SESSION['signup_error'] = "Internal Server Error!";
		header("Location: signup.php");
		exit();
	}

    $_SESSION['signup_temp'] = [
        'name' => $name,
        'email' => $email,
        'pass' => $pass
    ];

    $result = $auth->sendOtp($email,$name);

    if($result){
    	header("Location: enter_otp.php");
    	exit();
	}

	$_SESSION['signup_error'] = "Error sending OTP. Try again!";
	header("Location: signup.php");
	exit();
}


