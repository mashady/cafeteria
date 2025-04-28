<?php
include './includes/header.php';
include './db/connect.php';


if (isset($_POST["login"])){

  $emailErr = '';
  $passwordErr = '';
  $emailValue = $_POST['email'] ?? ''; 
  $passwordValue = $_POST['password'] ?? '';
  
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
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
    
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if ($user['password'] === md5($password)) {
                session_start();
                $_SESSION['user'] = $user;
                header("Location: index.php");
                exit();
            } else {
                $passwordErr = "Incorrect password";
            }
        } else {
            $emailErr = "invalid email";
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
    <title>log in</title>
  </head>
  <body>
  <style>
      
    </style>
  <div class="py-5">
    <div class="container">
    
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">

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
                <a href="./send_password.php" class="text-dark text-decoration-none">
                 Forget password?
                </a>
              </div>
              <div class="text-center mt-3">
                <a href="./register.php" class="text-dark text-decoration-none">
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
