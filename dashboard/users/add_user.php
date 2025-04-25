<?php
include '../../includes/header.php';
include '../../db/connect.php';

$nameErr = '';
$emailErr = '';
$passwordErr = '';
$confirmErr = '';
$roleErr = '';

$nameValue = '';
$emailValue = '';
$passwordValue = '';
$confirmPassword = '';
$roleValue = '';

if (isset($_POST["rbtn"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST["Name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirmPassword = $_POST["confirm_password"];
        $role = $_POST["role"];

        $nameValue = $name;
        $emailValue = $email;
        $roleValue = $role;

        // Validations
        if (empty($name)) {
            $nameErr = "Name is required";
        } elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
            $nameErr = "Name must contain only letters and spaces";
        }

        if (empty($email)) {
            $emailErr = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Please enter a valid email address";
        } else {
            $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
            $result_check = mysqli_query($conn, $sql_check_email);
            if (mysqli_num_rows($result_check) > 0) {
                $emailErr = "Email already exists. Please use a different email.";
            }
        }

        if (empty($password)) {
            $passwordErr = "Please enter a password";
        } elseif (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters long";
        }

        if (empty($confirmPassword)) {
            $confirmErr = "Please confirm your password";
        } elseif ($password !== $confirmPassword) {
            $confirmErr = "Passwords do not match";
        }

        if (empty($role) || !in_array($role, ['admin', 'user'])) {
            $roleErr = "Please select a valid role";
        }

        if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmErr) && empty($roleErr)) {
            $hashedPassword = md5($password); // 🔐 Consider using password_hash for better security

            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$nameValue', '$emailValue', '$hashedPassword', '$role')";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                echo '<div class="alert alert-success text-center mt-3">User added successfully! Redirecting...</div>';
                header("Location: index.php");
                exit();
            } else {
                echo '<div class="alert alert-danger text-center mt-3">Something went wrong. Please try again.</div>';
            }

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User - Admin Panel</title>

  <style>
    body {
      background-color: #f8f9fa;
    }
    .admin-panel {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card-custom {
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      background-color: #ffffff;
    }
    .card-header {
      background-color: #343a40;
      color: white;
    }
    .btn-dark {
      background-color: #343a40;
    }
    .form-label {
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="container admin-panel">
  <div class="col-md-8">
    <div class="card card-custom">
      <div class="card-header text-center">
        <h4 class="mb-0">👤 Add New User</h4>
      </div>
      <div class="card-body">
        <form method="post">

          <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="Name"
              class="form-control <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>"
              value="<?php echo htmlspecialchars($nameValue); ?>" />
            <div class="invalid-feedback"><?php echo $nameErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email"
              class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>"
              value="<?php echo htmlspecialchars($emailValue); ?>" />
            <div class="invalid-feedback"><?php echo $emailErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password:</label>
            <input type="password" name="password"
              class="form-control <?php echo (!empty($passwordErr)) ? 'is-invalid' : ''; ?>" />
            <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password:</label>
            <input type="password" name="confirm_password"
              class="form-control <?php echo (!empty($confirmErr)) ? 'is-invalid' : ''; ?>" />
            <div class="invalid-feedback"><?php echo $confirmErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Role:</label>
            <select name="role" class="form-select <?php echo (!empty($roleErr)) ? 'is-invalid' : ''; ?>">
              <option value="">-- Select Role --</option>
              <option value="user" <?php if ($roleValue == 'user') echo 'selected'; ?>>User</option>
              <option value="admin" <?php if ($roleValue == 'admin') echo 'selected'; ?>>Admin</option>
            </select>
            <div class="invalid-feedback"><?php echo $roleErr; ?></div>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" name="rbtn" class="btn btn-dark">➕ Add User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>


<?php include '../../includes/footer.php'; ?>
