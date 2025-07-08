<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Complete Payment</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body style="text-align:center; padding-top:100px;">
  <h2>Hello, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
  <p>Please complete the payment to continue using the app.</p>
  <button id="payBtn" style="padding:10px 20px; font-size:18px;">Pay ₹500</button>

  <script>
    document.getElementById("payBtn").addEventListener("click", () => {
      fetch("./create_order.php")
        .then(res => res.json())
        .then(data => {
          const options = {
            key: data.key,
            order_id: data.order_id,
            name: "Task Tracker App",
            description: "Access Subscription",
            prefill: {
              name: data.name,
              email: data.email
            },
            callback_url: "http://localhost:8080/verify.php"
          };
          const rzp = new Razorpay(options);
          rzp.open();
        })
        .catch(() => alert("Error creating Razorpay order"));
    });
  </script>
</body>
</html>
