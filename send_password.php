<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Africa/Cairo');

require 'vendor/autoload.php'; // If you used Composer

// Database connection
include './includes/header.php';
include './db/connect.php';

// Initialize message variables
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a random token
        $expiry = date("Y-m-d H:i:s", strtotime('+2 hour'));

        // Save token
        $conn->query("UPDATE users SET reset_token='$token', reset_token_expire='$expiry' WHERE email='$email'");

        // Send reset email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'am201121999@gmail.com'; // SMTP username
            $mail->Password = 'xtkm trxm ypix ztzg'; // SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('am201121999@gmail.com', 'Your App Name');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Request';
            $mail->Body = "Click <a href='http://localhost/cafeteria/reset_password.php?token=$token'>here</a> to reset your password.";

            $mail->send();
            $message = "A reset link has been sent to your email.";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Mailer Error: {$mail->ErrorInfo}";
            $message_type = "danger";
        }
    } else {
        $message = "Email not found.";
        $message_type = "danger";
    }
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
     <div class="col-md-6">
      <div class="card">
        <div class="card-body">

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST"> 
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input 
                    type="text" 
                    class="form-control" 
                    name="email"
                    value=""
                    placeholder="Enter your email"
                    required
                />
            </div>
        
            <button type="submit" name="login" class="btn w-100 btn-dark">Send Reset Link</button>
        </form>

        </div>
      </div>
    </div>
  </div>
</div>
