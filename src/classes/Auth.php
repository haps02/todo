<?php

use Mailgun\Mailgun;

class Auth {
	private $conn;
	
	public function __construct($conn){
		$this->conn = $conn;
	}

	public function sendPayConfirmation($email,$name){
		require_once __DIR__ . '/../vendor/autoload.php';
		$config = require('./config/secret_config.php');

		$apiKey = $config['MAILGUN_API_KEY'];
	    $domain = $config['MAILGUN_DOMAIN'];

	    try {
	        $mg = Mailgun::create($apiKey);

	        $response = $mg->messages()->send($domain, [
	            'from'    => 'Task Tracker <no-reply@' . $domain . '>',
	            'to'      => "$name <$email>",
	            'subject' => 'Payment Confirmation - Task Tracker App',
	            'html'    => "<p>Hello <strong>$name</strong>,</p>
	                          <p>Thank you for your payment!</p>
	                          <p>We have successfully received your payment and your access to the Task Tracker features is now active.</p>
	                          <p>If you have any questions or need assistance, feel free to reach out.</p>
	                          <br/>
	                          <p>Happy productivity!<br/>- Task Tracker Team</p>"
	        ]);

	        return true;
	    } catch (\Exception $e) {
	        error_log("Mailgun Error: " . $e->getMessage());
	        return false;
	    }
	}

	public function sendOtp($email,$name,$login=false){
		// session_start();
		require_once __DIR__ . '/../vendor/autoload.php';
		$config = require('./config/secret_config.php');

	    $otp = random_int(100000, 999999); // Generate 6-digit OTP
	    $_SESSION['otp'] = $otp;

	    $apiKey = $config['MAILGUN_API_KEY'];
	    $domain = $config['MAILGUN_DOMAIN'];

	    $subject = $login ? 'Your OTP for Login' : 'Your OTP for Email Verification';
	    $body = "<p>Hello <strong>$name</strong>,</p>
	             <p>Your OTP for " . ($login ? "login" : "email verification") . " is:</p>
	             <h2 style='color: #007bff;'>$otp</h2>
	             <p>This OTP is valid for 5 minutes. Please do not share it with anyone.</p>
	             <br/><p>- Task Tracker Team</p>";

	    try {
	        $mg = Mailgun::create($apiKey);

	        $mg->messages()->send($domain, [
	            'from'    => 'Task Tracker <no-reply@' . $domain . '>',
	            'to'      => "$name <$email>",
	            'subject' => $subject,
	            'html'    => $body
	        ]);

	        return true;
	    } catch (\Exception $e) {
	        error_log("Mailgun Error: " . $e->getMessage());
	        return false;
	    }
	}

	public function checkUser($email){
		try {
			$stmt = $this->conn->prepare("SELECT name FROM users WHERE email = ?;");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$result = $stmt->get_result();
			
			if($result->num_rows > 0){
				return ["USER_EXIST",$result];
			}
			else{
				return ["USER_NOT_EXIST",""];
			}
		}
		catch(mysqli_sql_exception $e) {
			return ["SQL_ERROR",""];
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
