<?php
include '../../includes/header.php';
include '../../db/connect.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Admin Rooms Dashboard</h1>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center ">
            <h5 class="mb-0">Rooms List</h5>
            <a href="add.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Room
            </a>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="">
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM rooms ORDER BY id ASC";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['name'] . "</td>";
                                echo "<td>
                                        <a href='edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-primary me-1' title='Edit'>
                                            <i class='fas fa-edit'>
                                            </i>
                                        </a>
                                        <button type='button' class='btn btn-sm btn-outline-danger' data-bs-toggle='modal' data-bs-target='#deleteModal".$row['id']."' title='Delete'>
                                            <a href='delete.php?id=" . $row['id'] . "'  style='color: inherit; text-decoration: none;'>
                                            <i class='fas fa-trash'></i>
                                        </a>    
                                        
                                        </button>
                                      </td>";
                                echo "</tr>";
                                
                                echo '
                                <div class="modal fade" id="deleteModal'.$row['id'].'" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete the category <strong>"'.$row['name'].'"</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <a href="delete.php?id='.$row['id'].'" class="btn btn-danger">Confirm Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No rooms found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>