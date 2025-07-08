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

    $email = $_SESSION['email'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?;");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();
            
    $user = $res->fetch_assoc();
            
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['is_paid'] = $user['is_paid'];

    unset($_SESSION['otp']);
    echo "SUCCESS";
    
    $db->closeConnection();

}
else {
    echo "INVALID";
}
