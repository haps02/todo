<?php
session_start();

$config = require('./config/secret_config.php');
require('./razorpay-php/razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['email'])) {
  http_response_code(401);
  echo json_encode(["error" => "Unauthorized"]);
  exit();
}

$keyId = $config['RAZORPAY_KEY_ID'];
$keySecret = $config['RAZORPAY_KEY_SECRET'];

try {
  $api = new Api($keyId, $keySecret);
  $userId = $_SESSION['user_id'];
  $name = $_SESSION['name'];
  $email = $_SESSION['email'];
  $amount = 50000; // ₹500 in paise

  $order = $api->order->create([
    'receipt' => 'receipt_' . $userId,
    'amount' => $amount,
    'currency' => 'INR',
    'payment_capture' => 1
  ]);

  require_once './classes/Database.php';
  $db = new Database($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
  $conn = $db->getConnection();

  $stmt = $conn->prepare("INSERT INTO orders (user_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, 'created')");
  if (!$stmt) {
    throw new Exception("Prepare failed");
  }

  $orderId = $order['id'];
  $stmt->bind_param("isi", $userId,$orderId, $amount);
  if (!$stmt->execute()) {
    throw new Exception("DB Insert failed");
  }

  $_SESSION['razorpay_order_id'] = $order['id'];

  echo json_encode([
    'key' => $keyId,
    'order_id' => $order['id'],
    'name' => $name,
    'email' => $email
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
