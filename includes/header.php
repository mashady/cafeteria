<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cafeteria System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="/cafeteria">Cafeteria</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav ms-auto">

        <?php if (isset($_SESSION['user'])): ?>

            <li class="nav-item">
              <a class="nav-link" href="/cafeteria/user_orders.php">My Orders</a>
            </li>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/orders">Orders</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/products">Products</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/categories">Categories</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/rooms">Rooms</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/users">Users</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/cafeteria/dashboard/checks">Checks</a>
                </li>
            <?php endif; ?>
            

            <li class="nav-item">
              <a class="nav-link" href="/cafeteria/logout.php">Logout</a>
            </li>

        <?php else: ?>

            <li class="nav-item">
              <a class="nav-link" href="/cafeteria/login.php">Login</a>
            </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
