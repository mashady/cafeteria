<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

$sql = "SELECT * FROM categories";
$result = mysqli_query($conn, $sql);

function validateName($name) {
    return preg_match('/^[A-Z][A-Za-z0-9\-\s]{1,15}$/', $name);
}

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    return in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024;
}

$errors = [];
$image_name = ''; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = $_POST['category'] ?? '';

    if (!validateName($name)) {
        $errors['name'] = "Product name must start with an uppercase letter and contain only letters, numbers, dashed, and spaces. It should be between 2 to 16 characters long.";
    }

    if (!is_numeric($price) || $price <= 0) {
        $errors['price'] = "Price must be a positive number.";
    }

    if (empty($category)) {
        $errors['category'] = "Category is required.";
    }

    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        if (!validateImage($_FILES['image'])) {
            $errors['image'] = "Only JPEG, PNG, GIF images allowed and max 2MB.";
        } else {
            $upload_dir = '../../assets/images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_path);
        }
    } else {
        $errors['image'] = "Product image is required.";
    }

    if (empty($errors)) {
        $name = mysqli_real_escape_string($conn, $name);
        $price = mysqli_real_escape_string($conn, $price);
        $category = mysqli_real_escape_string($conn, $category);
        $image_name = mysqli_real_escape_string($conn, $image_name);

        $sql = "INSERT INTO products (name, price, category_id, image) 
                VALUES ('$name', '$price', '$category', '$image_name')";

        if (mysqli_query($conn, $sql)) {
            header("Location: add_product.php?success=1");  
            exit;
        } else {
            echo "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
        }

        mysqli_close($conn);
    }
}
?>

<div class="container w-50 mt-5">
    <h1 class="">Create Product</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> Please ensure all fields are filled in correctly.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success" id="successAlert">
        <strong>Success:</strong> Product added successfully!
    </div>
    
    <script type="text/javascript">
        setTimeout(function() {
            window.location.href = "index.php";  
        }, 3000);  
    </script>
<?php endif; ?>


    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" id="name" value="<?= $_POST['name'] ?? '' ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="text-danger mt-1"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Product Price</label>
            <input type="number" name="price" class="form-control" id="price" value="<?= $_POST['price'] ?? '3.50 EGP' ?>" step="0.01" min="0">
            <?php if (!empty($errors['price'])): ?>
                <div class="text-danger mt-1"><?= $errors['price'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <div class="d-flex justify-content-between align-items-center">
            <select name="category" id="category" class="form-select w-75">
                <option value="">Select Category</option>
                <?php while ($category = mysqli_fetch_assoc($result)) { ?>
                    <option value="<?= $category['id'] ?>" <?= ($_POST['category'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                        <?= $category['name'] ?>
                    </option>
                <?php } ?>
             
            </select>
            <a href="../categories/add.php" class="btn btn-outline-primary btn-sm py-2">
                <i class="fas fa-plus"></i> Add New Category
            </a>
            </div>
            <?php if (!empty($errors['category'])): ?>
                <div class="text-danger mt-1"><?= $errors['category'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group mb-3">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
            <?php if (!empty($errors['image'])): ?>
                <div class="text-danger mt-1"><?= $errors['image'] ?></div>
            <?php endif; ?>
        </div>

        <div class="my-5 d-flex ">
            <button type="submit" class="btn btn-primary px-5">Add</button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
