<?php
// include '../includes/.php';
include './includes/header.php';
include './db/connect.php';
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display them on the page
ini_set('display_startup_errors', 1); // Also startup errors
$nameErr = '';
$emailErr = '';
$nameValue = '';
$emailValue = '';
$passwordErr = '';
$passwordValue = ''; 
$confirmPassword = '';
$confirmErr = '';
$uploadDir = 'assets/images/users/';
$imageNewName = '';
// $profile_image = '';
// $uploadDir = 'assets/images/users';
if (isset($_POST["rbtn"])){
 

$password=$_POST["password"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name=trim($_POST["Name"]);
    $email=trim($_POST["email"]);
    $password=$_POST["password"];
    $confirmPassword=$_POST["confirm_password"];
    $nameValue = $_POST["Name"];
    $emailValue = $_POST["email"];
    // $profile_image= $_POST["profile-image"];
    if (empty($name)) {
        $nameErr = "name is required";
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
    } elseif (strlen($password) < 6){
        $passwordErr = "Password must be at least 6 characters long";
    } 
    if (empty($confirmPassword)) {
        $confirmErr = "Please confirm your password";
    } elseif ($password !== $confirmPassword) {
        $confirmErr = "Passwords do not match";
    }
    if (isset($_FILES['profile-image']) && $_FILES['profile-image']['error'] == 0) {
      $fileName = $_FILES["profile-image"]["name"];
      $fileTmpName = $_FILES["profile-image"]["tmp_name"];
      $fileSize = $_FILES["profile-image"]["size"];
      $fileType = $_FILES["profile-image"]["type"];
  
      if ($fileSize > 2 * 1024 * 1024) {
          $imageErr = "File size exceeds the allowed limit (2MB).";
      } else {
          $fileArray = explode(".", $fileName);
          $lastElementExt = strtolower(end($fileArray));
          $allowedExt = ["jpg", "jpeg", "png", "gif"];
          if (!in_array($lastElementExt, $allowedExt)) {
              $imageErr = "Please upload a valid image file (jpg, jpeg, png, gif).";
          } else {
              $imageNewName = uniqid('', true) . '.' . $lastElementExt;
              $imageUploadPath = $uploadDir . $imageNewName;
              if (!move_uploaded_file($fileTmpName, $imageUploadPath)) {
                  $imageErr = "Error uploading image. Please try again.";
              }
          }
      }
  }
  


    if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmErr)&& empty($imageErr)) {
        $hashedPassword = md5($password);

        $sql = "INSERT INTO users (name,email,password, profile_pic) VALUES ('$nameValue', '$emailValue', '$hashedPassword', '$imageNewName')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $registrationSuccess = true;
            echo '<div class="alert alert-success text-center mt-3 w-50 mx-auto">Registration successful! Redirecting to login...</div>';
            echo '<meta http-equiv="refresh" content="1;url=login.php">';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style=" background-image: url('download.jpeg');">
    
<div class="container mt-5">
  <div class="row justify-content-center">
     <div class="col-md-6">
      <div class="card">
        <div class="card-body">
       
         
            <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
  <label class="form-label">Name:</label>
  <input 
    type="text" 
    class="form-control <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($nameErr)) ? 'is-invalid' : ''; ?>" 
    name="Name"
    value="<?php echo htmlspecialchars($nameValue ?? ''); ?>"
  />
  <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($nameErr)) : ?>
    <div class="invalid-feedback">
      <?php echo $nameErr; ?>
    </div>
  <?php endif; ?>
</div>
<div class="mb-3">
  <label class="form-label">Email:</label>
  <input 
    type="text" 
    class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>" 
    name="email"
    value="<?php echo $emailValue; ?>"
  />
  <?php if (!empty($emailErr)): ?>
    <div class="invalid-feedback">
      <?php echo $emailErr; ?>
    </div>
  <?php endif; ?>
</div>

<div class="mb-3">
  <label class="form-label">Password:</label>
  <input 
    type="password" 
    class="form-control <?php echo (!empty($passwordErr)) ? 'is-invalid' : ''; ?>" 
    name="password"
  />
  <?php if (!empty($passwordErr)): ?>
    <div class="invalid-feedback">
      <?php echo $passwordErr; ?>
    </div>
  <?php endif; ?>
</div>

<div class="mb-3">
  <label class="form-label">Confirm Password:</label>
  <input 
  type="password" 
  class="form-control <?php echo (!empty($confirmErr)) ? 'is-invalid' : ''; ?>" 
  name="confirm_password"
/>
  <?php if (!empty($confirmErr)): ?>
    <div class="invalid-feedback">
      <?php echo $confirmErr; ?>
    </div>
  <?php endif; ?>
</div>
<div class="mb-3">
    <input type="file" class="form-control" aria-label="file example" name="profile-image" >
    <div class="invalid-feedback">Example invalid form picture</div>
  </div>

              <button type="submit" name="rbtn" class="btn w-100 btn-dark" > Register </button>
            </form>

            <div class="text-center mt-3">
              <a href="./login.php" name="btn-login"
                class="text-dark text-decoration-none"
              >
                Already have an account?
                login

              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>



<?php include './includes/footer.php'; ?>