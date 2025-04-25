<?php
// include '../includes/.php';
include './includes/header.php';
include './db/connect.php';
$nameErr = '';
$emailErr = '';
$nameValue = '';
$emailValue = '';
$passwordErr = '';
$passwordValue = ''; 
$confirmPassword = '';
$confirmErr = '';
if (isset($_POST["rbtn"])){
 

$password=$_POST["password"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name=trim($_POST["Name"]);
    $email=trim($_POST["email"]);
    $password=$_POST["password"];
    $confirmPassword=$_POST["confirm_password"];
    $nameValue = $_POST["Name"];
    $emailValue = $_POST["email"];
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
    } elseif (strlen($password) < 8){
        $passwordErr = "Password must be at least 6 characters long";
    } 
    if (empty($confirmPassword)) {
        $confirmErr = "Please confirm your password";
    } elseif ($password !== $confirmPassword) {
        $confirmErr = "Passwords do not match";
    }
    if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmErr)) {
        $hashedPassword = md5($password);

        $sql = "INSERT INTO users (name,email,password) VALUES ('$nameValue', '$emailValue', '$hashedPassword')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $registrationSuccess = true;
            echo '<div class="alert alert-success text-center mt-3">Registration successful! Redirecting to login...</div>';
            echo '<meta http-equiv="refresh" content="3;url=login.php">';
            exit(); // Stop script to prevent form from reloading
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
       
         
            <form method="post" >

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

              <button type="submit" name="rbtn" class="btn w-100 btn-dark" > Register </button>
            </form>

            <div class="text-center mt-3">
              <a href="./login.php" name="btn-login"
                class="text-dark text-decoration-none"
              >
                Already have an account?
                <span class="ms-2 btn btn-dark px-3">login</span>
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