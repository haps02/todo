<?php
session_start();
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Log In</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">

  <div class="card p-4 shadow" style="width: 100%; max-width: 450px;">
    <h3 class="text-center mb-4">Log In</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tabs navs -->
    <ul class="nav nav-tabs mb-3" id="loginTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="password-tab" data-mdb-toggle="tab" data-mdb-target="#password-login" type="button" role="tab" aria-controls="password-login" aria-selected="true">
          Password Login
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="otp-tab" data-mdb-toggle="tab" data-mdb-target="#otp-login" type="button" role="tab" aria-controls="otp-login" aria-selected="false">
          OTP Login
        </button>
      </li>
    </ul>

    <!-- Tabs content -->
    <div class="tab-content" id="loginTabsContent">
      <!-- Password Login -->
      <div class="tab-pane fade show active" id="password-login" role="tabpanel" aria-labelledby="password-tab">
        <form method="POST" action="login_handler.php">
          <div class="form-outline mb-4">
            <input type="email" name="email" id="email" class="form-control" required />
            <label class="form-label" for="email">Email</label>
          </div>

          <div class="form-outline mb-4">
            <input type="password" name="pass" id="pass" class="form-control" required />
            <label class="form-label" for="pass">Password</label>
          </div>

          <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>
      </div>

      <!-- OTP Login -->
      <div class="tab-pane fade" id="otp-login" role="tabpanel" aria-labelledby="otp-tab">
        <form method="POST" action="login_otp_handler.php">
          <div class="form-outline mb-4">
            <input type="email" name="otp_email" id="otp_email" class="form-control" required />
            <label class="form-label" for="otp_email">Email</label>
          </div>

          <button type="submit" class="btn btn-warning w-100">Send OTP</button>
        </form>
      </div>
    </div>

    <p class="text-center mt-3">Don't have an account? <a href="signup.php">Sign Up</a></p>
  </div>

</body>
</html>
