<?php
include './includes/header.php';
include './db/connect.php';


if (isset($_POST["login"])){

  $emailErr = '';
  $passwordErr = '';
  $emailValue = '';
  $passwordValue = '';
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $email = trim($_POST["email"]);
      $password = $_POST["password"];
  
      
      if (empty($email)) {
          $emailErr = "Email is required";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Please enter a valid email address";
      }
  
      if (empty($password)) {
          $passwordErr = "Password is required";
      }
  
      if (empty($emailErr) && empty($passwordErr)) {
          $hashedPassword = md5($password);

          $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$hashedPassword'";
          $result = mysqli_query($conn, $sql);
  
          if (mysqli_num_rows($result) > 0) {
              session_start();
              $_SESSION['user'] = $email; 
              header("Location: user_home.php"); 
              exit();
          } else {
              $passwordErr = "Incorrect email or password";
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
  <title>Log In</title>
  <style>
    .card {
  background-image: url('./assets/images/login/images.jpeg');
 
  background-position: center;
  background-repeat: no-repeat;
}
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6); /* Dark overlay */
      z-index: 1;
    }

    .card-body {
      position: relative;
      z-index: 2;
      padding: 2rem;
    }

    .form-control {
      background-color: rgba(255, 255, 255, 0.9);
      color: #000;
    }

    .form-label,
    .invalid-feedback,
    a {
      color: #fff;
    }

    .btn-dark {
      background-color: #000;
      border: none;
    }

    .btn-dark:hover {
      background-color: #222;
    }
  </style>
</head>
<body>
  <div class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-9">
          <div class="card shadow-lg">
            <div class="card-body">
              <form method="post">
                <div class="mb-3">
                  <label class="form-label">Email:</label>
                  <input 
                      type="text" 
                      class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>" 
                      name="email"
                      value="<?php echo htmlspecialchars($emailValue ?? ''); ?>"
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

                <button type="submit" name="login" class="btn w-100 btn-dark">Login</button>
              </form>

              <div class="text-center mt-3">
                <a href="./register.php" class="text-decoration-none">
                  Don't have an account? Register
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>





<?php include './includes/footer.php'; ?>
