<?php
include './db/connect.php';
include './includes/header.php';
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expire > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // تحقق أن الحقول ليست فارغة أولاً
            if (empty($password) || empty($confirm_password)) {
                echo "<div class='alert alert-danger'>Both password fields are required.</div>";
            }
            elseif ($password !== $confirm_password) {
                echo "<div class='alert alert-danger'>Passwords do not match.</div>";
            } else {
                $new_password = md5($password);

                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE reset_token = ?");
                $update->bind_param("ss", $new_password, $token);

                if ($update->execute()) {
                    echo "<div class='alert alert-success'>Password has been updated. You can now <a href='login.php' class='alert-link'>login</a>.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
                }
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No token provided.</div>";
}
?>

<?php if (isset($result) && $result->num_rows > 0): ?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <form method="POST" class="needs-validation">
            <div class="mb-3">
              <label class="form-label">New Password:</label>
              <input 
                type="password" 
                class="form-control" 
                name="password" 
                placeholder="Enter new password"
              >
              <div class="invalid-feedback">
                Please enter a new password.
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password:</label>
              <input 
                type="password" 
                class="form-control" 
                name="confirm_password" 
                placeholder="Confirm new password"
              >
              <div class="invalid-feedback">
                Please confirm your password.
              </div>
            </div>
            <button type="submit" class="btn w-100 btn-dark">Reset Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
