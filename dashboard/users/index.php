<?php
include '../../includes/header.php';
include '../../db/connect.php';

include '../../includes/admin_auth.php';


$nameFilter = trim($_GET['name'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$where = [];
if ($nameFilter !== '') {
    $where[] = "name LIKE '%" . mysqli_real_escape_string($conn, $nameFilter) . "%'";
}
if ($roleFilter !== '') {
    $where[] = "role = '" . mysqli_real_escape_string($conn, $roleFilter) . "'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$limit = 5;  
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS total FROM users $whereSQL";
$countRes = mysqli_query($conn, $countSql);
$totalRow = mysqli_fetch_assoc($countRes);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $limit);

$sql = "SELECT * FROM users $whereSQL ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$params = $_GET;
unset($params['page']);
$baseQS = http_build_query($params);
?>

<style>
  .card-header { background-color: #0d6efd; color: white; }
  .table th { background-color: #f8f9fa; }
  .pagination .page-link { border: none; }
  .pagination .page-item.active .page-link { background-color: #0d6efd; }
</style>

<div class='container w-75 mt-5'>
  <h1 class=" mb-3">Admin Users Dashboard</h1>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-8">
      <input type="text" name="name" class="form-control" placeholder="Search by name" value="<?=htmlspecialchars($nameFilter)?>">
    </div>
    <div class="col-md-2">
      <select name="role" class="form-select">
        <option value="">All Roles</option>
        <option value="user" <?= $roleFilter=='user' ? 'selected' : ''?>>User</option>
        <option value="admin" <?= $roleFilter=='admin' ? 'selected' : ''?>>Admin</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
    </div>
  </form>

  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: white; color: #000;">
      <h5 class="m-2 fs-5">Users List</h5>
      <a href="add_user.php" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add New User
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped mt-3 align-middle table-bordered">
          <thead>
            <tr>
              <th>Name</th>
              <th>Image</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($user = mysqli_fetch_assoc($result)): 
              $id = htmlspecialchars($user["id"]); ?>
              <tr>
                <td><?=htmlspecialchars($user['name'])?></td>
                <td>
                  <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../../assets/images/users/<?=htmlspecialchars($user['profile_pic'])?>" 
                         alt="<?=htmlspecialchars($user['name'])?>" 
                         width="50" height="50" 
                         style="object-fit: cover; border-radius: 8px;">
                  <?php else: ?>
                    <span class="text-muted">No image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                    <?=ucfirst($user['role'])?>
                  </span>
                </td>
                <td>
                  <a href="edit_user.php?userid=<?=$id?>" class="btn btn-sm btn-outline-primary me-2">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?=$id?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>

              <div class="modal fade" id="deleteModal<?=$id?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Delete User</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      Confirm deletion of <strong><?=htmlspecialchars($user['name'])?></strong>?
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <a href="delete_user.php?userid=<?=$id?>" class="btn btn-danger">Delete</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?=$baseQS?>&page=<?=$page-1?>">
                prev
              </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?=$baseQS?>&page=<?=$i?>"><?=$i?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?=$baseQS?>&page=<?=$page+1?>">
                next
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>