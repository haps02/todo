<?php
session_start();
$error = $_SESSION['signup_error'] ?? null;
unset($_SESSION['signup_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">

  <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
    <h3 class="text-center mb-4">Sign Up</h3>
    
     <?php if ($error): ?>
    	<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
     <?php endif; ?>
    
    <form method="POST" action="signup_handler.php">
	  <div class="form-outline mb-4">
	    <input type="text" name="name" id="name" class="form-control" maxlength="100" required />
	    <label class="form-label" for="name">Name</label>
	  </div>

	  <div class="form-outline mb-4">
	    <input type="email" name="email" id="email" class="form-control" maxlength="100" required />
	    <label class="form-label" for="email">Email</label>
	  </div>

	  <div class="form-outline mb-4">
	    <input type="password" name="pass" id="pass" class="form-control" maxlength="100" required />
	    <label class="form-label" for="pass">Password</label>
	  </div>

	  <button type="submit" class="btn btn-primary w-100">Sign Up</button>
	  
	  <p class="text-center mt-3">Already have an account? <a href="login.php">Log In</a></p>
    </form>

  </div>

</body>
</html>

