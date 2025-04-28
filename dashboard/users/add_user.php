<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

$nameErr = '';
$emailErr = '';
$passwordErr = '';
$confirmErr = '';
$roleErr = '';
$imageErr="";

$nameValue = '';
$emailValue = '';
$passwordValue = '';
$confirmPassword = '';
$roleValue = '';
$uploadDir = '../../assets/images/users/';
$imageNewName = '';

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

        if (isset($_FILES['profile-image']) && $_FILES['profile-image']['error'] == 0) {
          $fileName = $_FILES["profile-image"]["name"];
          $fileTmpName = $_FILES["profile-image"]["tmp_name"];
          $fileSize = $_FILES["profile-image"]["size"];
  
          $fileArray = explode(".", $fileName);
          $lastElementExt = strtolower(end($fileArray));
          $allowedExt = ["jpg", "jpeg", "png", "gif"];
  
          if ($fileSize > 2 * 1024 * 1024) {
              $imageErr = "File size exceeds the allowed limit (2MB).";
          } elseif (!in_array($lastElementExt, $allowedExt) || !getimagesize($fileTmpName)) {
              $imageErr = "Please upload a valid image file (jpg, jpeg, png, gif).";
          } else {
              $imageNewName = uniqid('', true) . '.' . $lastElementExt;
              $imageUploadPath = $uploadDir . $imageNewName;
              if (!move_uploaded_file($fileTmpName, $imageUploadPath)) {
                  $imageErr = "Error uploading image. Please try again.";
              }
          }
      } else {
          $imageErr = "Please upload a profile image.";
      }
      

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
        
        
     
    

      if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmErr) && empty($roleErr) && empty($imageErr)) {
        $hashedPassword = md5($password);
        $sql = "INSERT INTO users (name, email, password, profile_pic,role) 
        VALUES ('$nameValue', '$emailValue', '$hashedPassword', '$imageNewName','$roleValue')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $registrationSuccess = true;
            echo '<div class="alert alert-success text-center mt-3 w-50 mx-auto">user add successful! Redirecting to login...</div>';
            echo '<meta http-equiv="refresh" content="1;url=index.php">';
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
    
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="Name"
              class="form-control <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>"
              value="<?php echo htmlspecialchars($nameValue); ?>" />
            <div class="invalid-feedback"><?php echo $nameErr; ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="text" name="email"
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
          <div class="mb-3">
            <label class="form-label">Profile Image:</label>
            <input type="file" name="profile-image"
                   class="form-control <?php echo (!empty($imageErr)) ? 'is-invalid' : ''; ?>">
            <div class="invalid-feedback"><?php echo $imageErr; ?></div>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" name="rbtn" class="btn btn-dark">Add User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>


<?php include '../../includes/footer.php'; ?>