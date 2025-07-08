<?php session_start(); 

if (!isset($_SESSION['otp'])) {
    $_SESSION['signup_error'] = "Session expired. Please sign up again.";
    header("Location: signup.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-3 text-center">Email Verification</h4>
        <form id="otpForm">
            <div class="form-outline mb-4">
                <input type="text" id="otp" name="otp" class="form-control" required />
                <label class="form-label" for="otp">Enter the OTP sent to your email</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
        </form>
        <div id="result" class="mt-3 text-center"></div>
    </div>

    <script>
    document.getElementById("otpForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch("verify_otp.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(msg => {
            const result = document.getElementById("result");
            if (msg === "SUCCESS") {
                result.innerHTML = "<span class='text-success'>OTP Verified ✅</span>";
                setTimeout(() => {
                    window.location.href = "payment.php";
                }, 1500);
            } else if (msg === "INVALID") {
                result.innerHTML = "<span class='text-danger'>Invalid OTP ❌</span>";
            } else {
                result.innerHTML = "<span class='text-warning'>Session expired. Please sign up again.</span>";
            }
        });
    });
    </script>
</body>
</html>
