<?php
include '../../includes/header.php';
include '../../db/connect.php';
?>
<?php 
    $id = $_GET["id"]; 
    $sql= "select * from categories where id = $id";
    $cat =  mysqli_query( $conn, $sql); 
    $category_name = mysqli_fetch_assoc($cat);

    if(isset($_POST["btn"])){
        $name = $_POST["name"];
        $sql = "update categories set name= '$name' where id = $id";
        mysqli_query( $conn, $sql); 
        header("location: index.php"); 
    }
?> 

<div class="container">
<h1>New category</h1>

<form method="POST" class="w-50">
  <div class="mb-3">
    <label for="exampleInputPassword1" class="form-label">Name</label>
    <input type="text" name="name" class="form-control"
    value= <?php echo $category_name["name"] ?>
    >
  </div>
  <button type="submit" name="btn" class="btn btn-primary">Submit</button>
</form>
</div>
