<?php

session_start();

$emailId = $_SESSION['signup_temp']['email'];
$name = $_SESSION['signup_temp']['name'];

require_once('./classes/Auth.php');
require_once('./classes/Database.php');

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$auth = new Auth($conn);

$result = $auth->sendOtp($name,$emailId);

header("Location: enter_otp.php");
exit();