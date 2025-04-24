<?php
include '../../includes/header.php';
include '../../db/connect.php';

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

?>


<div class='container w-75 mt-5'>

    <h1 class="text-center text-muted mb-3">Admin Users dashboard</h1>  
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center ">
            <h5 class="m-2 fs-5">All Users</h5>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    
        <div class="card-body">
            <div class="table-responsive">   
                <table class='table table-striped mt-3 text-center align-middle'>
                    <tr>
                        <th>Name</th>
                        <th>Room</th>
                        <th>Image</th>
                        <th>Ext</th>
                        <th colspan="2">Action</th>
                    </tr>
                    <?php
                        while($users = mysqli_fetch_assoc($result)) {
                            echo "<tr >"; 
                                echo "<td> $users[name] </td>";
                                echo "<td> $users[room] </td>";
                                echo "<td> <img src='../../assets/images/users/$users[profile_pic]' alt='$users[name]' width='50' height='50' style='object-fit: cover; border-radius: 8px;'> </td>";
                                echo "<td> $users[ext] </td>";
                                $id = htmlspecialchars($users["id"]);
                                echo "<td>   <a href='edit.php?userid=$id'class='btn btn-outline-warning border border-0' ><i class='fas fa-edit fs-5'></i> </a>   </td>"; 
                                echo "<td>   <a href='delete.php?userid=$id' class='btn btn-outline-danger border border-0'> <i class='fas fa-trash fs-5'></i> </a>   </td>"; 

                            echo "</tr>"; 
                        }
                    ?> 
            </table>  
        </div>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
