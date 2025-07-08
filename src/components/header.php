<?php
session_start();

if (!isset($_SESSION['name'])) {
  echo <<<HTML
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Not Logged In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" />
  </head>
  <body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="text-center">
      <h2 class="mb-4">You are not logged in</h2>
      <div class="d-flex justify-content-center gap-3">
        <a href="login.php" class="btn btn-primary">Log In</a>
        <a href="signup.php" class="btn btn-outline-primary">Sign Up</a>
      </div>
    </div>
  </body>
  </html>
  HTML;
  exit();
}
?>

<?php if (!isset($_SESSION['is_paid']) || $_SESSION['is_paid'] !== 1): ?>
  <div class="alert alert-warning alert-dismissible fade show mb-0 text-center" role="alert" style="border-radius: 0;">
    <strong>Payment Required:</strong> You have not paid the fee. You won't be able to use features until payment is completed.
    <a href="payment.php" class="btn btn-sm btn-warning ms-3">Pay Now</a>
    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo $pageTitle ?? "Dashboard"; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body class="bg-light">
  <div class="container my-4">
    <!-- 🧭 Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><?php echo htmlspecialchars($pageTitle ?? "Dashboard"); ?> - <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
      <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>

