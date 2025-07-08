<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Auth {
	private $conn;
	
	public function __construct($conn){
		$this->conn = $conn;
	}

	public function sendOtp($name,$email){
		// session_start();
		require_once __DIR__ . '/../vendor/autoload.php';
		$config = require('./config/secret_config.php');

	    $otp = random_int(100000, 999999); // Generate 6-digit OTP
	    $_SESSION['otp'] = $otp;

		$mail = new PHPMailer(true);

	    try {
	        // Server settings
	        $mail->isSMTP();
	        $mail->Host = $config['SMTP_HOST'];
	        $mail->SMTPAuth = true;
	        $mail->Username = $config['SMTP_USERNAME'];
	        $mail->Password = $config['SMTP_PASSWORD'];
	        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	        $mail->Port = 25;

	        // From address
	        $mail->setFrom('john.doe@example.org', 'John Doe');
	        $mail->addAddress($email, $name);

	        // Email content
	        $mail->isHTML(true);
	        $mail->Subject = 'Your OTP for Email Verification';
	        $mail->Body    = "<p>Hello <strong>$name</strong>,</p>
	                          <p>Your OTP for email verification is:</p>
	                          <h2 style='color: #007bff;'>$otp</h2>
	                          <p>This OTP is valid for 5 minutes. Please do not share it with anyone.</p>
	                          <br/><p>- Task Tracker Team</p>";

	        $mail->send();
	        return true;
	    } catch (Exception $e) {
	        error_log("Mailer Error: {$mail->ErrorInfo}");
	        return false;
	    }
	}

	public function checkUser($email){
		try {
			$stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?;");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$stmt->store_result();
			
			if($stmt->num_rows > 0){
				return "USER_EXIST";
			}
			else{
				return "SUCCESS";
			}
		}
		catch(mysqli_sql_exception $e) {
			return "SQL_ERROR";
		}
	}
	
	public function signup($name,$email,$pass){
		try {
			$stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?;");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$stmt->store_result();
			
			if($stmt->num_rows > 0){
				return "USER_EXIST";
			}
			
			$hashed_pass = password_hash($pass,PASSWORD_DEFAULT);
			$stmt = $this->conn->prepare("INSERT INTO users (name,email,pass) VALUES (?, ?, ?);");
			$stmt->bind_param("sss",$name,$email,$hashed_pass);
			$stmt->execute();
			
			session_regenerate_id(true);
			return "SUCCESS";
		}
		catch(mysqli_sql_exception $e){
			//echo "Error: " . $e->getMessage();
			return "SQL_ERROR";
		}
	}
	
	public function login($email, $pass){
		try {	
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?;");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$result = $stmt->get_result();
			
			if($result->num_rows > 0){
				$user = $result->fetch_assoc();
				if(password_verify($pass,$user['pass'])){
					session_regenerate_id(true);
					return $user['id'];
				}
				else{
					return "WRONG_PASSWORD";
				}
			}
			else{
				return "USER_NOT_EXIST";
			}
		}
		catch(mysqli_sql_exception $e){
			return "SQL_ERROR";
		}
	}
}
