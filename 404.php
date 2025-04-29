<?php
http_response_code(404);

require_once __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found</title>
    <!-- Include your CSS files here -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error-container h1 {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-container p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .error-container a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .error-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <p>It might have been moved or deleted.</p>
        <a href="index.php">Return to Homepage</a>
    </div>

    <?php
    require_once __DIR__ . '/includes/footer.php';
    ?>
</body>
</html>