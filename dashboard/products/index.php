<?php
include '../../includes/header.php';
include '../../db/connect.php';

$sql = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

?>


<div class="container">
</div>
<div class='container w-75 mt-5'>

<h1 class="text-center text-muted">Admin products dashboard</h1>  
<div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center ">
            <h5 class="mb-0">All Products</h5>
            <a href="add_product.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
    
 <div class="card-body">
  <div class="table-responsive">   
    <table class='table table-striped table-hover mt-3 text-center'>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Image</th>
            <th colspan="3">Action</th>
            <!-- <th>Available</th>
            <th>Delete</th>
            <th>Edit</th> -->

        </tr>
        
        <?php
            while($product = mysqli_fetch_assoc($result)) {
                echo "<tr class ='align-middle'>"; 
                    echo "<td> $product[name] </td>";
                    echo "<td> $product[price] EGP</td>";
                    echo "<td> <img src='../../assets/images/products/$product[image]' alt='$product[name]' width='50' height='50' style='object-fit: cover; border-radius: 8px;'> </td>";
                    $id = htmlspecialchars($product["id"]);
                    echo "<td>   <a href='available_product.php?product_id=$id' class='btn  btn-outline-success me-1 border border-0'> <i class='fas fa-check fs-5'></i> </a>   </td>"; 


                    echo "<td>   <a href='edit_product.php?product_id=$id'  class='btn  btn-outline-warning me-1 border border-0' title='Edit'><i class='fas fa-edit fs-5'></i></a>   </td>";
                    echo "<td>   <a href='delete_product.php?product_id=$id' class='btn  btn-outline-danger me-1 border border-0' title='Delete'> <i class='fas fa-trash fs-5'></i> </a>   </td>"; 
 
                echo "</tr>"; 

            }
        ?> 
    </table>  
   </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
