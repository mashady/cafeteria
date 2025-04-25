<?php
include '../../includes/header.php';
include '../../db/connect.php';

function validationName($name) {
    return preg_match('/^[A-Z][a-zA-Z\s]{2,20}$/', $name);
}

function validationEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validationPassword($password){
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password);
}

function matchConfirmPassword($password, $confirmPassword){
    return $password === $confirmPassword;
}

function validationExt($ext){
    return preg_match('/^[0-9]{1,5}$/', $ext);
}

$errors = [];
$success_message = '';
$name = $email = $password = $confirmPassword = $ext = $room = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $room = $_POST['room'] ?? '';
    $ext = trim($_POST['ext'] ?? '');
    $role = $_POST['role'] ?? '';
    $profile_pic = $_FILES['profile_pic'] ?? null;

    // Validate inputs
    if (!validationName($name)) {
        $errors['name'] = "Name must start with an uppercase letter and contain only letters and spaces. It should be between 2 to 20 characters long.";
    }

    if (!validationEmail($email)) {
        $errors['email'] = "Invalid email format.";
    }

    if (!validationPassword($password)) {
        $errors['password'] = "Password must be at least 8 characters long and contain at least one letter and one number.";
    }

    if (!matchConfirmPassword($password, $confirmPassword)) {
        $errors['confirmPassword'] = "Passwords do not match.";
    }

    if (!validationExt($ext)) {
        $errors['ext'] = "Ext must be a number between 1 to 5 digits.";
    }

    // Validate profile picture
    if ($profile_pic && $profile_pic['error'] === 0) {
        $extType = strtolower(pathinfo($profile_pic['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extType, $allowedExts) || $profile_pic['size'] > 2 * 1024 * 1024) {
            $errors['profile_pic'] = "Only JPG, PNG, GIF images allowed and max size 2MB.";
        }
    } else {
        $errors['profile_pic'] = "Please upload a valid image.";
    }

    // If no errors, process data
    if (empty($errors)) {
        $upload_dir = '../../assets/images/users/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = uniqid() . '_' . basename($profile_pic['name']);
        move_uploaded_file($profile_pic['tmp_name'], $upload_dir . $image_name);

        // Escape data
        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, password_hash($password, PASSWORD_BCRYPT));
        $room = mysqli_real_escape_string($conn, $room);
        $ext = mysqli_real_escape_string($conn, $ext);
        $role = mysqli_real_escape_string($conn, $role);
        $profile_pic = mysqli_real_escape_string($conn, $image_name);

        // Insert into database
        $sql = "INSERT INTO users (name, email, password, profile_pic, role, room, ext) 
                VALUES ('$name', '$email', '$password', '$profile_pic', '$role', '$room', '$ext')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $success_message = "User added successfully.";
            header("Location: index.php?message=" . urlencode($success_message));
            exit;
        } else {
            $errors['db'] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<div class="container w-50 mt-5">
    <h1 class="text-center text-muted">Add New User</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mt-3" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success mt-3" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" id="name" value="<?= htmlspecialchars($name) ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="text" name="email" class="form-control" id="email" value="<?= htmlspecialchars($email) ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" id="password" autocomplete="new-password">
        </div>

        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" autocomplete="new-password">
        </div>

        <div class="mb-3">
            <label for="RoomNo" class="form-label">Room No.</label>
            <select name="room" id="RoomNo" class="form-select">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($room == $i) ? 'selected' : '' ?>>Room <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="Ext" class="form-label">Ext.</label>
            <input type="text" name="ext" class="form-control" id="Ext" value="<?= htmlspecialchars($ext) ?>">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select">
                <option value="User" <?= ($role == 'User') ? 'selected' : '' ?>>User</option>
                <option value="Admin" <?= ($role == 'Admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="profile_pic" class="form-label">Profile Picture</label>
            <input type="file" name="profile_pic" class="form-control" id="profile_pic" accept="image/*">
        </div>

        <div class="mb-3 justify-content-evenly d-flex">
            <button type="submit" name="btn" class="btn btn-success px-5">Add</button>
            <button type="reset" name="reset" class="btn btn-warning px-5">Reset</button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php' ?>
