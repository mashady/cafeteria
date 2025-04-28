<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

$id = $_GET["userid"];
$sql = "select * from users where id = $id";
$result = mysqli_query($conn, $sql);
$row=mysqli_fetch_assoc($result);
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

if (isset($_POST["btn"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST["Name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $nameValue = $name;
        $emailValue = $email;
        

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


        if (empty($nameErr) && empty($emailErr) && empty($passwordErr)) {
            
            $update_sql = "UPDATE users SET Name = '$name', email = '$email'";

            if (!empty($password)) {
                $hashedPassword = md5($password);
                $update_sql .= ", password = '$hashedPassword'";
            }
    
            $update_sql .= " WHERE id = $id";
    
            if (mysqli_query($conn, $update_sql)) {
                echo '<div class="alert alert-success text-center mt-3">User updated successfully! Redirecting...</div>';
                header("refresh:2;url=index.php");
                exit();
            } else {
                echo '<div class="alert alert-danger text-center mt-3">Update failed. Try again.</div>';
            }
        
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>update User - Admin Panel</title>

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
    
      <div class="card-body">
        <form method="post">

          <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="Name"
              class="form-control <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>"
              value="<?php echo $row["name"] ;?>" />
            <div class="invalid-feedback"><?php echo $nameErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="text" name="email"
              class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>"
              value="<?php echo $row["email"] ?>" />
            <div class="invalid-feedback"><?php echo $emailErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password:</label>
            <input type="password" name="password"
              class="form-control <?php echo (!empty($passwordErr)) ? 'is-invalid' : ''; ?>"/>
            <div class="invalid-feedback"><?php echo $passwordErr; ?></div>
          </div>


          <div class="d-grid mt-4">
            <button type="submit" name="btn" class="btn btn-dark">update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>


<?php include '../../includes/footer.php'; ?>
