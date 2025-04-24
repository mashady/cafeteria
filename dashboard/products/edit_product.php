<?php
    $id = $_GET["product_id"];  // Get the product ID from the URL

    include '../../includes/header.php';
    include '../../db/connect.php';

    // get the product details
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    // Check if the product exists
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='alert alert-danger'>Product not found.</div>";
        exit;
    }

    // get the categories
    $sql1 = "SELECT * FROM categories";
    $result1 = mysqli_query($conn, $sql1);


// Validation functions
function validateName($name) {
    return preg_match('/^[A-Z][A-Za-z0-9\-\s]{1,15}$/', $name);
}

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    return in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024;
}

$errors = [];
$image_name = ''; // Default image name


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = $_POST['category'] ?? '';

    // Validate inputs
    if (!validateName($name)) {
        $errors['name'] = "Product name must start with an uppercase letter and contain only letters, numbers, dashed, and spaces. It should be between 2 to 16 characters long.";
    }

    if (!is_numeric($price) || $price <= 0) {
        $errors['price'] = "Price must be a positive number.";
    }

    if (empty($category)) {
        $errors['category'] = "Category is required.";
    }

    // Validate image if uploaded
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

    

// If no errors, update product in database
if (empty($errors)) {
        if (empty($image_name)) {
            $image_name = $product['image'];
        }

        // Escape data
        $name = mysqli_real_escape_string($conn, $name);
        $price = mysqli_real_escape_string($conn, $price);
        $category = mysqli_real_escape_string($conn, $category);
        $image_name = mysqli_real_escape_string($conn, $image_name);

        // Update product in database
        $sql = "UPDATE products SET 
                    name = '$name',
                    price = '$price',
                    category_id = '$category',
                    image = '$image_name'
                WHERE id = $id";
 
        if (mysqli_query($conn, $sql)) {
            header("Location: edit_product.php?product_id=$id&success=1");  // Redirect to the same page with success message product_id=$id&success=1"
            exit;
        } else {
            echo "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
        }

        mysqli_close($conn);
    }
}
?>

<div class="container w-50 mt-5">
    <h1 class="text-center text-muted">Edit Product</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> Please ensure all fields are filled in correctly.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success" id="successAlert">
        <strong>Success:</strong> Product Updated successfully!
    </div>
    
    <script type="text/javascript">
        setTimeout(function() {
            window.location.href = "index.php";  // Redirect to the products page after 3 seconds
        }, 3000);  
    </script>
<?php endif; ?>


    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" id="name" value="<?= $_POST['name'] ?? $product['name'] ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="text-danger mt-1"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Product Price</label>
            <input type="number" name="price" class="form-control" id="price" value="<?= $_POST['price'] ?? $product['price'] ?>" step="0.01" min="0">
            <?php if (!empty($errors['price'])): ?>
                <div class="text-danger mt-1"><?= $errors['price'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <div class="d-flex justify-content-between align-items-center">
            <select name="category" id="category" class="form-select w-75">
                <option value="">Select Category</option>
                <?php while ($category = mysqli_fetch_assoc($result1)) { ?>
                    <option value="<?= $category['id'] ?>" <?= ($_POST['category'] ?? $product['category_id']) == $category['id'] ? 'selected' : '' ?>>
                        <?= $category['name'] ?>
                    </option>
                <?php } ?>
             
            </select>
            <a href="add_category.php" class="btn btn-outline-primary btn-sm py-2">
                <i class="fas fa-plus"></i> Add New Category
            </a>
            </div>
            <?php if (!empty($errors['category'])): ?>
                <div class="text-danger mt-1"><?= $errors['category'] ?></div>
            <?php endif; ?>
        </div>

        
        <div class="form-group mb-3">
            <label for="image">Product Image</label>
            <?php if (!empty($product['image'])): ?>
                <div class="mb-2">
                    <img src="../../assets/images/products/<?= $product['image'] ?>" alt="<?=$product['name'] ?? 'product' ?>" style="max-width: 150px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form-control" accept="image/*" value="<?= $_POST['image'] ?? $product['image'] ?>">
            <?php if (!empty($errors['image'])): ?>
                <div class="text-danger mt-1"><?= $errors['image'] ?></div>
            <?php endif; ?>
        </div>

        <div class="my-5 d-flex justify-content-evenly">
            <button type="submit" class="btn btn-success px-5">Update</button>
            <button type="reset" class="btn btn-warning px-5">Reset</button>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
