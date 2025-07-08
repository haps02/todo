<?php
session_start();

$config = require('./config/secret_config.php');
require_once "./classes/Database.php";
require_once "./classes/Auth.php";

if (!isset($_SESSION['otp'])) {
    echo "SESSION_EXPIRED";
    exit();
}

$enteredOtp = $_POST['otp'];
$actualOtp = $_SESSION['otp'];

$db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
$conn = $db->getConnection();

$auth = new Auth($conn);

if ($enteredOtp == $actualOtp) {

    $name = $_SESSION['signup_temp']['name'];
    $email = $_SESSION['signup_temp']['email'];
    $pass = $_SESSION['signup_temp']['pass'];

    $result = $auth->signup($name,$email,$pass);
    
    if($result === "SUCCESS"){
         $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?;");
         $stmt->bind_param("s",$email);
         $stmt->execute();
         $res = $stmt->get_result();
            
         $user = $res->fetch_assoc();
            
         $_SESSION['user_id'] = $user['id'];
         $_SESSION['email'] = $email;
         $_SESSION['name'] = $name;

         unset($_SESSION['signup_temp']);
         unset($_SESSION['otp']);
         echo "SUCCESS";

    }
    else if($result === "USER_EXIST"){
         $_SESSION['signup_error'] = "User already exist! Try to login";
         header("Location: signup.php");
    }
    else{
         $_SESSIOIN['signup_error'] = "Internal Server Error!";
         header("Location: signup.php");
    }
    
    $db->closeConnection();

    }
else {
    echo "INVALID";
}
