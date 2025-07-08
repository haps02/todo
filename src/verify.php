<?php
session_start();

$config = require('./config/secret_config.php');
require('./razorpay-php/razorpay-php/Razorpay.php'); // Composer autoload OR direct include
use Razorpay\Api\Api;

$api_key = $config['RAZORPAY_KEY_ID'];
$api_secret = $config['RAZORPAY_KEY_SECRET'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;

    $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? null;
    $razorpay_order_id = $_POST['razorpay_order_id'] ?? null;
    $razorpay_signature = $_POST['razorpay_signature'] ?? null;

    if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
        $success = false;
    } else {
        try {
            $api = new Api($api_key, $api_secret);
            $attributes = [
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature
            ];
            $api->utility->verifyPaymentSignature($attributes);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            $success = false;
        }
    }

    if ($success) {
        // Payment verified successfully
        // You can now mark the user as "paid" in your database

        require_once './classes/Database.php';
        $db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
        $conn = $db->getConnection();

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("UPDATE users SET is_paid = 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $db->closeConnection();

            $_SESSION['is_paid'] = 1;

            // echo "✅ Payment successful!";

            header("Location: dashboard.php");
        }
        else {
            echo "<h3>Error: User session not found</h3>";
        }
    } else {
        echo "<h3>❌ Payment verification failed. Please try again.</h3>";
    }
} else {
    echo "<h3>Invalid request</h3>";
}

