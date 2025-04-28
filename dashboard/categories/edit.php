<?php
include '../../includes/header.php';
include '../../db/connect.php';
include '../../includes/admin_auth.php';

$id = $_GET['id'];
$error = '';
$name = '';

$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if(isset($_POST["btn"])){
    $name = trim($_POST["name"]);
    
    // Validation
    if(empty($name)) {
        $error = "Please provide a valid category name (1-100 characters).";
    } elseif(strlen($name) > 100) {
        $error = "Please provide a valid category name (1-100 characters).";
    } else {
        $check = "SELECT * FROM categories WHERE name = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "si", $name, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Category name already exists!";
        } else {
            $sql = "UPDATE categories SET name = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $name, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: index.php");
                exit();
            } else {
                $error = "Error updating category: " . mysqli_error($conn);
            }
        }
    }
} else {
    $name = $category['name'];
}
?>

<div class="container mt-4">
    <h1>Edit Category</h1>
    
    <form method="POST" class="w-50 needs-validation" novalidate>
        <div class="mb-3">
            <label for="categoryName" class="form-label">Name</label>
            <input type="text" 
                   name="name" 
                   class="form-control <?php echo !empty($error) ? 'is-invalid' : ''; ?>" 
                   id="categoryName"
                   value="<?php echo htmlspecialchars($name); ?>"
                   required
                   maxlength="100">
            <div class="invalid-feedback">
                <?php echo $error ?: 'Please provide a valid category name (1-100 characters).'; ?>
            </div>
        </div>
        <button type="submit" name="btn" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
(function () {
    'use strict'
    
    var forms = document.querySelectorAll('.needs-validation')
    
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                form.querySelectorAll('.is-invalid').forEach(function(el) {
                    el.classList.remove('is-invalid');
                });
                
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>