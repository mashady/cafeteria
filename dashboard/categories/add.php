<?php
include '../../includes/header.php';
include '../../db/connect.php';
?>
<?php
        if(isset($_POST["btn"])){
            $name = $_POST["name"];
            $check = "SELECT * FROM categories WHERE name = '$name'"; 
            $result = mysqli_query($conn, $check);
            if (mysqli_num_rows($result) > 0) {
                echo '<div class="alert alert-warning w-50" style="margin-left:50px;">Category name already exists!</div>';
            }else{
                $sql = "insert into categories (name) values ( '$name' )"; 
                mysqli_query($conn, $sql);
                header("location: index.php"); 
            }
        }
?>

<div class="container">
<h1>New category</h1>

<form method="POST" class="w-50">
  <div class="mb-3">
    <label for="exampleInputPassword1" class="form-label">Name</label>
    <input type="text" name="name" class="form-control">
  </div>
  <button type="submit" name="btn" class="btn btn-primary">Submit</button>
</form>
</div>
