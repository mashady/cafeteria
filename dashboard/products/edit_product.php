<?php
$id = $_GET["product_id"];

include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

// 1. Fetch product
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
} else {
    echo "<div class='alert alert-danger'>Product not found.</div>";
    exit;
}

// 2. Fetch categories
$sql1 = "SELECT * FROM categories";
$result1 = mysqli_query($conn, $sql1);

// 3. Validation Functions
function validateName($name) {
    return preg_match('/^[A-Z][A-Za-z0-9\\-\\s]{1,15}$/', $name);
}

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    return in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024;
}

$errors = [];
$image_name = ''; 

// 4. Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = $_POST['category'] ?? '';

    // Validate fields
    if (!validateName($name)) {
        $errors['name'] = "Product name must start with an uppercase letter and be 2-16 characters long.";
    }

    if (!is_numeric($price) || $price <= 0) {
        $errors['price'] = "Price must be a positive number.";
    }

    if (empty($category)) {
        $errors['category'] = "Category is required.";
    }

    // Handle Image
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

            // Delete old image
            if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                unlink($upload_dir . $product['image']);
            }
        }
    } else {
        // No new image uploaded, keep old image
        $image_name = $product['image'];
    }

    // Update if no errors
    if (empty($errors)) {
        $name = mysqli_real_escape_string($conn, $name);
        $price = mysqli_real_escape_string($conn, $price);
        $category = mysqli_real_escape_string($conn, $category);
        $image_name = mysqli_real_escape_string($conn, $image_name);

        $sql = "UPDATE products SET 
                    name = '$name',
                    price = '$price',
                    category_id = '$category',
                    image = '$image_name'
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: edit_product.php?product_id=$id&success=1");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
        }

        mysqli_close($conn);
    }
}
?>

<!-- HTML FORM -->
<div class="container w-50 mt-5">
    <h1 class="text-center text-muted">Edit Product</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> Please ensure all fields are correct.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success" id="successAlert">
            <strong>Success:</strong> Product updated successfully!
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
        </script>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" name="name" id="name" class="form-control" 
                   value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="text-danger mt-1"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Price -->
        <div class="mb-3">
            <label for="price" class="form-label">Product Price</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>">
            <?php if (!empty($errors['price'])): ?>
                <div class="text-danger mt-1"><?= $errors['price'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Category -->
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <div class="d-flex justify-content-between align-items-center">
                <select name="category" id="category" class="form-select w-75">
                    <option value="">Select Category</option>
                    <?php while ($cat = mysqli_fetch_assoc($result1)): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($_POST['category'] ?? $product['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <a href="add_category.php" class="btn btn-outline-primary btn-sm py-2">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>
            <?php if (!empty($errors['category'])): ?>
                <div class="text-danger mt-1"><?= $errors['category'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Image -->
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <?php if (!empty($product['image'])): ?>
                <div class="mb-2">
                    <img src="../../assets/images/products/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name'] ?? 'product') ?>" 
                         style="max-width: 150px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
            <?php if (!empty($errors['image'])): ?>
                <div class="text-danger mt-1"><?= $errors['image'] ?></div>
            <?php endif; ?>
        </div>

        <!-- Buttons -->
        <div class="my-5 d-flex justify-content-evenly">
            <button type="submit" class="btn btn-success px-5">Update</button>
            <button type="reset" class="btn btn-warning px-5">Reset</button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
