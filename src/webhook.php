<?php

$config = require('./config/secret_config.php');
require('./razorpay-php/razorpay-php/Razorpay.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

require_once './classes/Database.php';

$webhookSecret = $config['WEBHOOK_SECRET']; // Replace with your actual webhook secret from Razorpay

// Read the incoming POST from Razorpay
$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// Log the raw payload
file_put_contents("webhook_log.txt", "Raw Payload:\n" . $body . "\n\n", FILE_APPEND);

try {

    $api_key = $config['RAZORPAY_KEY_ID'];
    $api_secret = $config['RAZORPAY_KEY_SECRET'];
    // Verify signature
    $api = new Api($api_key, $api_secret);
    $api->utility->verifyWebhookSignature($body, $signature, $webhookSecret);

    $data = json_decode($body, true);
    $event = $data['event'] ?? '';
    file_put_contents("webhook_log.txt", "Event: $event\n", FILE_APPEND);

    if ($event === "payment.captured") {
        $paymentEntity = $data['payload']['payment']['entity'];

        $paymentId = $paymentEntity['id'];
        $orderId = $paymentEntity['order_id'];
        $amount = $paymentEntity['amount'];

        file_put_contents("webhook_log.txt", "Payment ID: $paymentId\nOrder ID: $orderId\n", FILE_APPEND);

        // Connect to DB
        $db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
        $conn = $db->getConnection();
        $conn->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

        // Check if order exists
        $stmt = $conn->prepare("SELECT user_id FROM orders WHERE razorpay_order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $userId = $result['user_id'];

            // Update orders table with payment ID
            $stmt = $conn->prepare("UPDATE orders SET razorpay_payment_id=?, status='paid' WHERE razorpay_order_id=?");
            $stmt->bind_param("ss", $paymentId, $orderId);
            $stmt->execute();

            // Mark user as paid
            $stmt = $conn->prepare("UPDATE users SET is_paid = 1 WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            file_put_contents("webhook_log.txt", "Updated user $userId as paid.\n", FILE_APPEND);
        } else {
            file_put_contents("webhook_log.txt", "Order not found for Order ID: $orderId\n", FILE_APPEND);
        }

        $db->closeConnection();
    } else {
        file_put_contents("webhook_log.txt", "Ignored event type: $event\n", FILE_APPEND);
    }

    http_response_code(200);
    echo "OK";
} catch (SignatureVerificationError $e) {
    file_put_contents("webhook_log.txt", "Signature Verification Failed: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo "Signature verification failed.";
} catch (Exception $e) {
    file_put_contents("webhook_log.txt", "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo "Internal Server Error.";
}
